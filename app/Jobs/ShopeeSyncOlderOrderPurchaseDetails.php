<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use App\Traits\ShopeeOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ShopeeSyncOlderOrderPurchaseDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $api_limit = 100;
            $shopee_shops = Shopee::get();
            if (sizeof($shopee_shops) == 0) {
                Log::debug("no shopee shops found");
                return;
            }
            foreach ($shopee_shops as $index => $shop) {
                $order_purchases = $this->getOldOrderPurchases($shop["id"]);
                if (sizeof($order_purchases) == 0) {
                    Log::debug("No old shopee orders found \"".$shop->shop_name."\"");
                    continue;
                }

                $ordersn_list = [];
                foreach ($order_purchases as $order_purchase) {
                    if (!in_array($order_purchase["order_id"], $ordersn_list)) {
                        array_push($ordersn_list, $order_purchase["order_id"]);
                    }
                }

                $loop_counter = floor(sizeof($ordersn_list)/$api_limit);
                if (sizeof($ordersn_list)%$api_limit > 0) {
                    $loop_counter += 1;
                }

                $addtional_delay = $loop_counter > 2?true:false;
                for($i=0; $i<$loop_counter; $i++) {
                    $ordersn_list_for_job = array_slice($ordersn_list, $i*$api_limit, $api_limit);
                    $delay = $index*($i+1)*5;
                    if ($addtional_delay) {
                        $delay += 180; 
                    }  
                    if (sizeof($ordersn_list_for_job) > 0) {
                        ShopeeOrderDetailSync::dispatch($shop->shop_id, $shop->seller_id, $shop->id, $ordersn_list_for_job)->delay(Carbon::now()->addSeconds($delay)); 
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get old order for specific shop.
     */
    private function getOldOrderPurchases($website_id) {
        $orders = [];
        try {
            $orders = ShopeeOrderPurchase::select("order_id", "seller_id", "order_date", "status", "website_id")
                ->where("order_date", "<", Carbon::now()->subDays(10))
                ->where(function($query) {
                    $query->whereNotIn("status", [
                        ShopeeOrderPurchase::ORDER_STATUS_CANCELLED,
                        ShopeeOrderPurchase::ORDER_STATUS_COMPLETED
                    ])->orWhereNotIn("status_custom", [
                        strtolower(ShopeeOrderPurchase::ORDER_STATUS_CANCELLED),
                        strtolower(ShopeeOrderPurchase::ORDER_STATUS_COMPLETED)
                    ]);
                })
                ->whereWebsiteId($website_id)
                ->orderBy('id', 'asc')
                ->limit(200)
                ->get();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return $orders;
    }
}
