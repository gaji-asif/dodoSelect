<?php

namespace App\Jobs;

use App\Models\LazadaOrderPurchase;
use App\Traits\LazadaOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaSyncOlderOrderPurchaseDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $old_order_purchases = $this->getOldOrderPurchases();
            if (sizeof($old_order_purchases) == 0) {
                return;
            }

            $access_tokens_arr = [];
            foreach ($old_order_purchases as $index => $order_purchase) {
                if (!isset($access_tokens_arr[$order_purchase->website_id])) {
                    $access_tokens_arr[$order_purchase->website_id] = $this->getAccessTokenForLazada($order_purchase->website_id);
                }
                $access_token = $access_tokens_arr[$order_purchase->website_id];
                if (!empty($access_token)) {
                    /* Fetch order detail from Lazada */
                    LazadaSyncSpecificOrderPurchaseDetail::dispatch($order_purchase->website_id, $order_purchase->order_id, $order_purchase->seller_id, $access_token)->delay(Carbon::now()->addSeconds($index*5));
                    /* Fetch order item details from Lazada */
                    LazadaOrderPurchaseSyncOrderItemDetails::dispatch($order_purchase->website_id, $order_purchase->order_id, $access_token)->delay(Carbon::now()->addSeconds($index*5));
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function getOldOrderPurchases() {
        $orders = [];
        try {
            $orders = LazadaOrderPurchase::where("order_date", "<", Carbon::now()->subDays(15))
                ->whereIn("derived_status", [
                    LazadaOrderPurchase::ORDER_STATUS_PACKED,
                    LazadaOrderPurchase::ORDER_STATUS_PENDING,
                    LazadaOrderPurchase::ORDER_STATUS_READY_TO_SHIP,
                    LazadaOrderPurchase::ORDER_STATUS_SHIPPED
                ])
                ->limit(100)
                ->get();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return $orders;
    }
}
