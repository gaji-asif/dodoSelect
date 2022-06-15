<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ShopeeOrderSyncTrait
{
    /**
     * Check if bulk sync is allowed for a user at a particular time.
     */
    public function checkCanBulkSync($shopee_shop_id, $seller_id) 
    {
        try {
            if (isset($shopee_shop_id, $seller_id) and !empty($shopee_shop_id) and !empty($seller_id)) {
                if(Cache::has($this->getBulkSyncStartTimeCacheKey($shopee_shop_id, $seller_id)) and 
                    !empty($this->getBulkSyncStartTimeCacheKey($shopee_shop_id, $seller_id))) {
                    $start_time = Cache::get($this->getBulkSyncStartTimeCacheKey($shopee_shop_id, $seller_id));
                    if(!empty($start_time) and Carbon::parse($start_time)->diffInMinutes(Carbon::now()) < $this->getBulkSyncAllocatedTimeInMinutes()) {                            
                        return false;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return true;
    }


    /**
     * Get the key used in cache used in bulk sync start time for a particular shopee shop id and seller.
     */
    public function getBulkSyncStartTimeCacheKey($shopee_id, $seller_id) 
    {
        if (isset($shopee_id, $seller_id) and !empty($shopee_id) and !empty($seller_id)) {
            return "session_bulk_sync_key_start_time__".$shopee_id."_".$seller_id;
        }
        return "";
    }


    /**
     * Get the bulk sync start time for a particular shopee shop id and seller.
     */
    public function getBulkSyncStartTimeCacheValue($shopee_id, $seller_id) 
    {
        if (isset($shopee_id, $seller_id) and !empty($shopee_id) and !empty($seller_id)) {
            return Cache::get($this->getBulkSyncStartTimeCacheKey($shopee_id, $seller_id));
        }
        return "";
    }


    /**
     * Get the key used in cache used in bulk sync end time for a particular shopee shop id and seller.
     */
    public function getBulkSyncEndTimeCacheKey($shopee_id, $seller_id) 
    {
        if (isset($shopee_id, $seller_id) and !empty($shopee_id) and !empty($seller_id)) {
            return "session_bulk_sync_key_end_time__".$shopee_id."_".$seller_id;
        }
        return "";
    }


    /**
     * Get the bulk sync end time for a particular shopee shop id and seller.
     */
    public function getBulkSyncEndTimeCacheValue($shopee_id, $seller_id) 
    {
        if (isset($shopee_id, $seller_id) and !empty($shopee_id) and !empty($seller_id)) {
            return Cache::get($this->getBulkSyncEndTimeCacheKey($shopee_id, $seller_id));
        }
        return "";
    }


    /**
     * Get the key which is used by cache to get and set the 30 minute interval start time.
     */
    public function getIntervalWiseBulkSyncStartTimeCacheKey() 
    {
        return "session_interval_wise_bulk_sync_key_start_time";
    }
    

    /**
     * Get the time stored in cache when the 30 minute interval started last time.
     */
    public function getIntervalWiseBulkSyncStartTimeCacheValue() 
    {
        $datetime = Cache::get($this->getIntervalWiseBulkSyncStartTimeCacheKey());
        if (isset($datetime) and !empty($datetime)) {
            return $datetime;
        }
        $datetime = Carbon::now()->subMinutes(30)->format("Y-m-d H:i:s");
        $this->setIntervalWiseBulkSyncStartTimeCacheValue($datetime);
        return $datetime;
    }
    

    /**
     * Set the time in cache when the 30 minute interval starts each time.
     */
    public function setIntervalWiseBulkSyncStartTimeCacheValue($datetime="") 
    {
        if (!empty($datetime)) {
            Cache::put($this->getIntervalWiseBulkSyncStartTimeCacheKey(), $datetime, Carbon::now()->addHours(2));
        } else {
            Cache::put($this->getIntervalWiseBulkSyncStartTimeCacheKey(), Carbon::now()->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForShopeeOrderBulkSync()));
        }
    }


    /**
     * Allocated time for bulk sync.
     * By default 5 minutes.
     */
    public function getBulkSyncAllocatedTimeInMinutes() 
    {
        return 5;
    }


    /** 
     * Allocated time for cache used in bulk sync.
     * By default 2 hours.
     */
    public function getCacheExpirationPeriodForShopeeOrderBulkSync() 
    {
        return 2;
    }
}