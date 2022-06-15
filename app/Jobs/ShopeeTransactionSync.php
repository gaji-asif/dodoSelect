<?php

namespace App\Jobs;

use App\Models\ShopeeSetting;
use App\Models\ShopeeTransaction;
use App\Models\User;
use App\Traits\LineBotTrait;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Shopee\Client;
use Shopee\Nodes\Payment\Parameters\GetTransactionList;

class ShopeeTransactionSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use LineBotTrait;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /** @var ShopeeSetting */
    private $shopeeSetting;

    /** @var int */
    private $shopId;

    /** @var User */
    private $seller;

    /** @var array */
    private $requestParams;

    /**
     * Create a new job instance.
     *
     * @param  User  $seller
     * @return void
     */
    public function __construct(
        ShopeeSetting $shopeeSetting,
        int $shopId,
        User $seller,
        array $requestParams = []
    )
    {
        $this->shopeeSetting = $shopeeSetting;
        $this->shopId = $shopId;
        $this->seller = $seller;
        $this->requestParams = $requestParams;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $client = new Client([
                'baseUrl' => $this->shopeeSetting->host,
                'secret' => $this->shopeeSetting->parent_key,
                'partner_id' => (int) $this->shopeeSetting->parent_id,
                'shopid' => (int) $this->shopId,
            ]);

            $page = $this->requestParams['page'] ?? 0;
            $perPage = $this->requestParams['pagination_entries_per_page'] ?? 100;
            $createTimeFrom = $this->requestParams['create_time_from'] ?? strtotime('-15 days');
            $createTimeTo = $this->requestParams['create_time_to'] ?? strtotime('now');

            $paginationOffset = $page * $perPage;

            $requestParams = new GetTransactionList();
            $requestParams->setPaginationOffset($paginationOffset)
                ->setCreateTimeFrom($createTimeFrom)
                ->setCreateTimeTo($createTimeTo);

            $shopeeResponse = $client->payment
                ->getTransactionList($requestParams)
                ->getData();

            $transaction_list = $shopeeResponse['transaction_list'] ?? [];
            $hasMore = $shopeeResponse['has_more'] ?? false;

            if (!empty($transaction_list)) {
                $this->storeTransactionData($transaction_list);
            }

            if ($hasMore) {
                $page++;

                ShopeeTransactionSync::dispatch($this->shopeeSetting, $this->shopId, $this->seller, [
                    'page' => $page,
                    'create_time_from' => $createTimeFrom,
                    'create_time_to' => $createTimeTo
                ])->delay(now()->addSeconds(2));
            }

        } catch (\Exception $th) {
            report($th);

            $this->triggerPushMessage("Shop ID #{$this->shopId} : failed to start syncing transactions data from \"Shopee\"");
        }
    }

    /**
     * Store the translation_list data to the db
     * if ordersn already exists, update the data instead
     *
     * @param  array  $transaction_list
     * @return void
     */
    private function storeTransactionData(array $transaction_list = [])
    {
        try {
            DB::beginTransaction();

            $newShopeeTransactionData = [];

            foreach ($transaction_list as $transaction) {
                $transactionId = $transaction['transaction_id'] ?? '';

                if (empty($transactionId)) {
                    continue;
                }

                $exists = DB::table((new ShopeeTransaction())->getTable())
                    ->where([
                        'transaction_id' => $transactionId
                    ])
                    ->first();

                if (empty($exists)) {
                    $newShopeeTransactionData[] = [
                        'seller_id' => $this->seller->id,
                        'shop_id' => $this->shopId,
                        'transaction_id' => $transactionId,
                        'status' => $transaction['status'] ?? null,
                        'wallet_type' => $transaction['wallet_type'] ?? null,
                        'transaction_type' => $transaction['transaction_type'] ?? null,
                        'amount' => $transaction['amount'] ?? 0,
                        'current_balance' => $transaction['current_balance'] ?? 0,
                        'create_time' => $transaction['create_time'] ?? null,
                        'ordersn' => $transaction['ordersn'] ?? '',
                        'refund_sn' => $transaction['refund_sn'] ?? null,
                        'withdrawal_type' => $transaction['withdrawal_type'] ?? null,
                        'transaction_fee' => $transaction['transaction_fee'] ?? 0,
                        'description' => $transaction['description'] ?? null,
                        'buyer_name' => $transaction['buyer_name'] ?? null,
                        'pay_order_list' => json_encode($transaction['pay_order_list']) ?? null,
                        'withdraw_id' => $transaction['withdraw_id'] ?? null,
                        'reason' => $transaction['reason'] ?? null,
                        'root_withdrawal_id' => $transaction['root_withdrawal_id'] ?? null,
                        'created_at' => new DateTime()
                    ];
                } else {
                    DB::table((new ShopeeTransaction())->getTable())
                        ->where([
                            'transaction_id' => $transactionId
                        ])
                        ->update([
                            'status' => $transaction['status'] ?? null,
                            'wallet_type' => $transaction['wallet_type'] ?? null,
                            'transaction_type' => $transaction['transaction_type'] ?? null,
                            'amount' => $transaction['amount'] ?? 0,
                            'current_balance' => $transaction['current_balance'] ?? 0,
                            'create_time' => $transaction['create_time'] ?? null,
                            'ordersn' => $transaction['ordersn'] ?? '',
                            'refund_sn' => $transaction['refund_sn'] ?? null,
                            'withdrawal_type' => $transaction['withdrawal_type'] ?? null,
                            'transaction_fee' => $transaction['transaction_fee'] ?? 0,
                            'description' => $transaction['description'] ?? null,
                            'buyer_name' => $transaction['buyer_name'] ?? null,
                            'pay_order_list' => json_encode($transaction['pay_order_list']) ?? null,
                            'withdraw_id' => $transaction['withdraw_id'] ?? null,
                            'reason' => $transaction['reason'] ?? null,
                            'root_withdrawal_id' => $transaction['root_withdrawal_id'] ?? null,
                            'updated_at' => new DateTime()
                        ]);
                }
            }

            if (!empty($newShopeeTransactionData)) {
                DB::table((new ShopeeTransaction())->getTable())->insert($newShopeeTransactionData);
            }

            DB::commit();

        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->shopId}"
        ];
    }
}
