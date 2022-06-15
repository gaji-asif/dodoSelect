<?php

namespace App\Jobs;

use App\Models\ShopeeIncome;
use App\Models\ShopeeSetting;
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
use Shopee\Nodes\Order\Parameters\GetIncomeOfOrder;

class ShopeeOrderIncomeSync implements ShouldQueue
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

    /** @var string */
    private $ordersn;

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
        string $ordersn
    )
    {
        $this->shopeeSetting = $shopeeSetting;
        $this->shopId = $shopId;
        $this->seller = $seller;
        $this->ordersn = $ordersn;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $shopeeClient = new Client([
                'baseUrl' => $this->shopeeSetting->host,
                'secret' => $this->shopeeSetting->parent_key,
                'partner_id' => (int) $this->shopeeSetting->parent_id,
                'shopid' => (int) $this->shopId,
            ]);

            $requestParameters = new GetIncomeOfOrder();
            $requestParameters->setOrdersn($this->ordersn);

            $shopeeResponse = $shopeeClient->myincome->getIncomeOfOrder($requestParameters)->getData();
            $this->storeDataToDatabase($shopeeResponse);

        } catch (\Exception $th) {
            report($th);

            $this->triggerPushMessage("Shop ID #{$this->shopId} : failed to start syncing {$this->ordersn} income data from \"Shopee\"");
        }
    }


    /**
     * Store data to database
     *
     * @param  array  $data
     * @return void
     */
    public function storeDataToDatabase(array $data)
    {
        try {
            DB::beginTransaction();

            $newShopeeIncomeData = [];

            $exists = DB::table((new ShopeeIncome())->getTable())
                ->where([
                    'seller_id' => $this->seller->id,
                    'shop_id' => $this->shopId,
                    'ordersn' => $data['ordersn'] ?? ''
                ])
                ->first();

            $orderIncome = $data['order_income'] ?? [];

            if (! $exists) {
                $newShopeeIncomeData[] = [
                    'seller_id' => $this->seller->id,
                    'shop_id' => $this->shopId,
                    'ordersn' => $data['ordersn'] ?? '',
                    'buyer_user_name' => $data['buyer_user_name'] ?? '',
                    'returnsn_list' => json_encode($data['returnsn_list']) ?? null,
                    'refund_id_list' => json_encode($data['refund_id_list']) ?? null,
                    'escrow_amount' => $orderIncome['escrow_amount'] ?? 0,
                    'buyer_total_amount' => $orderIncome['buyer_total_amount'] ?? 0,
                    'original_price' => $orderIncome['original_price'] ?? 0,
                    'seller_discount' => $orderIncome['seller_discount'] ?? 0,
                    'shopee_discount' => $orderIncome['shopee_discount'] ?? 0,
                    'voucher_from_seller' => $orderIncome['voucher_from_seller'] ?? 0,
                    'voucher_from_shopee' => $orderIncome['voucher_from_shopee'] ?? 0,
                    'coins' => $orderIncome['coins'] ?? 0,
                    'buyer_paid_shipping_fee' => $orderIncome['buyer_paid_shipping_fee'] ?? 0,
                    'buyer_transaction_fee' => $orderIncome['buyer_transaction_fee'] ?? 0,
                    'cross_border_tax' => $orderIncome['cross_border_tax'] ?? 0,
                    'payment_promotion' => $orderIncome['payment_promotion'] ?? 0,
                    'commission_fee' => $orderIncome['commission_fee'] ?? 0,
                    'service_fee' => $orderIncome['service_fee'] ?? 0,
                    'seller_transaction_fee' => $orderIncome['seller_transaction_fee'] ?? 0,
                    'seller_lost_compensation' => $orderIncome['seller_lost_compensation'] ?? 0,
                    'seller_coin_cash_back' => $orderIncome['seller_coin_cash_back'] ?? 0,
                    'escrow_tax' => $orderIncome['escrow_tax'] ?? 0,
                    'final_shipping_fee' => $orderIncome['final_shipping_fee'] ?? 0,
                    'actual_shipping_fee' => $orderIncome['actual_shipping_fee'] ?? 0,
                    'shopee_shipping_rebate' => $orderIncome['shopee_shipping_rebate'] ?? 0,
                    'shipping_fee_discount_from_3pl' => $orderIncome['shipping_fee_discount_from_3pl'] ?? 0,
                    'seller_shipping_discount' => $orderIncome['seller_shipping_discount'] ?? 0,
                    'estimated_shipping_fee' => $orderIncome['estimated_shipping_fee'] ?? 0,
                    'seller_voucher_code' => json_encode($orderIncome['seller_voucher_code']) ?? null,
                    'drc_adjustable_refund' => $orderIncome['drc_adjustable_refund'] ?? 0,
                    'escrow_amount_aff' => $orderIncome['escrow_amount_aff'] ?? 0,
                    'exchange_rate' => $orderIncome['exchange_rate'] ?? 0,
                    'local_currency' => $orderIncome['local_currency'] ?? 0,
                    'escrow_currency' => $orderIncome['escrow_currency'] ?? 0,
                    'reverse_shipping_fee' => $orderIncome['reverse_shipping_fee'] ?? 0,
                    'created_at' => new DateTime()
                ];
            } else {
                DB::table((new ShopeeIncome())->getTable())
                    ->where([
                        'seller_id' => $this->seller->id,
                        'shop_id' => $this->shopId,
                        'ordersn' => $data['ordersn'] ?? ''
                    ])
                    ->update([
                        'seller_id' => $this->seller->id,
                        'shop_id' => $this->shopId,
                        'ordersn' => $data['ordersn'] ?? '',
                        'buyer_user_name' => $data['buyer_user_name'] ?? '',
                        'returnsn_list' => json_encode($data['returnsn_list']) ?? null,
                        'refund_id_list' => json_encode($data['refund_id_list']) ?? null,
                        'escrow_amount' => $orderIncome['escrow_amount'] ?? 0,
                        'buyer_total_amount' => $orderIncome['buyer_total_amount'] ?? 0,
                        'original_price' => $orderIncome['original_price'] ?? 0,
                        'seller_discount' => $orderIncome['seller_discount'] ?? 0,
                        'shopee_discount' => $orderIncome['shopee_discount'] ?? 0,
                        'voucher_from_seller' => $orderIncome['voucher_from_seller'] ?? 0,
                        'voucher_from_shopee' => $orderIncome['voucher_from_shopee'] ?? 0,
                        'coins' => $orderIncome['coins'] ?? 0,
                        'buyer_paid_shipping_fee' => $orderIncome['buyer_paid_shipping_fee'] ?? 0,
                        'buyer_transaction_fee' => $orderIncome['buyer_transaction_fee'] ?? 0,
                        'cross_border_tax' => $orderIncome['cross_border_tax'] ?? 0,
                        'payment_promotion' => $orderIncome['payment_promotion'] ?? 0,
                        'commission_fee' => $orderIncome['commission_fee'] ?? 0,
                        'service_fee' => $orderIncome['service_fee'] ?? 0,
                        'seller_transaction_fee' => $orderIncome['seller_transaction_fee'] ?? 0,
                        'seller_lost_compensation' => $orderIncome['seller_lost_compensation'] ?? 0,
                        'seller_coin_cash_back' => $orderIncome['seller_coin_cash_back'] ?? 0,
                        'escrow_tax' => $orderIncome['escrow_tax'] ?? 0,
                        'final_shipping_fee' => $orderIncome['final_shipping_fee'] ?? 0,
                        'actual_shipping_fee' => $orderIncome['actual_shipping_fee'] ?? 0,
                        'shopee_shipping_rebate' => $orderIncome['shopee_shipping_rebate'] ?? 0,
                        'shipping_fee_discount_from_3pl' => $orderIncome['shipping_fee_discount_from_3pl'] ?? 0,
                        'seller_shipping_discount' => $orderIncome['seller_shipping_discount'] ?? 0,
                        'estimated_shipping_fee' => $orderIncome['estimated_shipping_fee'] ?? 0,
                        'seller_voucher_code' => json_encode($orderIncome['seller_voucher_code']) ?? null,
                        'drc_adjustable_refund' => $orderIncome['drc_adjustable_refund'] ?? 0,
                        'escrow_amount_aff' => $orderIncome['escrow_amount_aff'] ?? 0,
                        'exchange_rate' => $orderIncome['exchange_rate'] ?? 0,
                        'local_currency' => $orderIncome['local_currency'] ?? 0,
                        'escrow_currency' => $orderIncome['escrow_currency'] ?? 0,
                        'reverse_shipping_fee' => $orderIncome['reverse_shipping_fee'] ?? 0,
                        'updated_at' => new DateTime()
                    ]);
            }

            if (! empty($newShopeeIncomeData)) {
                DB::table((new ShopeeIncome())->getTable())->insert($newShopeeIncomeData);
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
