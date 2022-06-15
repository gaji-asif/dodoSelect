<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Traits\ShopeeOrderSyncTrait;
use Illuminate\Support\Facades\Cache;
use JsonException;

class ShopeeOrderAutoSyncEveryInterval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderSyncTrait;
    private $number_of_orders;
    private $auth_id;
    private $force_process;
    private $shopee_api_limit = 100;
    private $session_time_limit;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($number_of_orders=0, $auth_id=0, $force_process=false)
    {
        $this->number_of_orders = $number_of_orders<=0?-1:$number_of_orders;
        $this->auth_id = $auth_id;
        $this->force_process = $force_process;
        $this->session_time_limit = $this->getBulkSyncAllocatedTimeInMinutes();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $shopee_shops = Shopee::get();
            if (sizeof($shopee_shops) == 0) {
                Log::debug("No shops found for \"Shopee\" order syncing");
                return;
            }
            $sellers = [];
            if ($this->auth_id != 0) {
                array_push($sellers, $this->auth_id);
            } else {
                $sellers = $this->getSellers();
            }
            if (sizeof($sellers) == 0) {
                Log::debug("No seller found for \"Shopee\" order syncing");
                return;
            }
            $this->setIntervalWiseBulkSyncStartTimeCacheValue();
            foreach ($shopee_shops as $index => $shopee_shop) {
                if (isset($shopee_shop,$shopee_shop->shop_id) and !empty($shopee_shop->shop_id)) {
                    foreach ($sellers as $seller_id) {
                        if(!$this->force_process and !$this->checkCanBulkSync($shopee_shop->id, $seller_id)) {
                            Log::debug("Not allowed to bulk sync order from \"".$shopee_shop->shop_name."\".");
                            continue;
                        }
                        /* Update the start and end time again in case delay from queue. */
                        Cache::put($this->getBulkSyncStartTimeCacheKey($shopee_shop->id, $seller_id), Carbon::now()->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForShopeeOrderBulkSync()));
                        Cache::put($this->getBulkSyncEndTimeCacheKey($shopee_shop->id, $seller_id), Carbon::now()->addMinutes($this->session_time_limit)->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForShopeeOrderBulkSync()));
                        ShopeeOrderSync::dispatch(0, $this->shopee_api_limit, (int) $shopee_shop->id, (int) $shopee_shop->shop_id, (int) $seller_id, $this->number_of_orders, "auto")->delay(now()->addSeconds($index*30));
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function getSellers() {
        $data = [];
        try {
            $sellers = ShopeeOrderPurchase::select("seller_id")->distinct()->get();
            foreach ($sellers as $seller) {
                array_push($data, $seller->seller_id);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return $data;
    }
}
