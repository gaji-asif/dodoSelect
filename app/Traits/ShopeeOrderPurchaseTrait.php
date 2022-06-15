<?php

namespace App\Traits;

use App\Models\ShopeeOrderPurchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ShopeeOrderPurchaseTrait
{
    use ShopeeTrait;

    /**
     * Process the selected time slot and correct the format for front end.
     */
    public function processShippedOnTimeTextForPickUp($time_slot_text) 
    {
        $time_text = "";
        $data = explode(" ", $time_slot_text);
        if (isset($data[0])) {
            $date = explode("-", $data[0]);
            if (sizeof($date) == 3) {
                $time_text = $date[2]."/".$date[1]."/".$date[0];
            } else {
                $time_text = $data[0];
            }
        }
        if (isset($data[1])) {
            $time_text .= " ".$data[1];
        }
        return $time_text;
    }


    public function putShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn, $status="processing") 
    {
        try {    
            if (isset($ordersn) and !empty($ordersn)) {
                Cache::put($this->getKeyPrefixForShopeeTrackingInit().$ordersn, $status, Carbon::now()->addMinutes(10));
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    public function getShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn) 
    {
        try {    
            if (isset($ordersn) and !empty($ordersn)) {
                if (Cache::has($this->getKeyPrefixForShopeeTrackingInit().$ordersn)) {
                    return Cache::get($this->getKeyPrefixForShopeeTrackingInit().$ordersn);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    public function removeShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn) 
    {
        try {    
            if (isset($ordersn) and !empty($ordersn)) {
                if (Cache::has($this->getKeyPrefixForShopeeTrackingInit().$ordersn)) {
                    Cache::forget($this->getKeyPrefixForShopeeTrackingInit().$ordersn);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    public function getKeyPrefixForShopeeTrackingInit() 
    {
        return "shopee_init_".$this->getShopeeSellerId()."_";
    }


    /**
     * Check for dupclicate orders.
     */
    private function checkForDuplicateEntries($id, $order_id) 
    {
        try {
            $order_purchases = ShopeeOrderPurchase::whereOrderId($order_id)->whereNotIn("id", [$id])->pluck("order_id", "id");
            if (sizeof($order_purchases) > 0) {
                foreach ($order_purchases as $opid => $order_purchase) {
                    ShopeeOrderPurchase::whereOrderId($order_id)->whereId($opid)->delete();
                }
            }
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Check for custom status is correct for "cancelled" and "completed".
     * If not update it.
     */
    private function crossMatchCustomStatusIsCorrect($id) 
    {
        try {
            $order_purchase = ShopeeOrderPurchase::find($id);
            if (isset($order_purchase)) {
                if (isset($order_purchase->status_custom) and 
                    in_array($order_purchase->status, [ShopeeOrderPurchase::ORDER_STATUS_COMPLETED, ShopeeOrderPurchase::ORDER_STATUS_CANCELLED]) and 
                    $order_purchase->status_custom != ShopeeOrderPurchase::determineStatusCustom($order_purchase->status, isset($order_purchase->tracking_number)?$order_purchase->tracking_number:"")) {
                    $order_purchase->status_custom = ShopeeOrderPurchase::determineStatusCustom($order_purchase->status, isset($order_purchase->tracking_number)?$order_purchase->tracking_number:"");
                    $order_purchase->save();
                }
            }
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function lockShopeeOrderBulkInitKey($auth_id) 
    {
        return 'lock_shopee_order_bulk_init__'.$auth_id;
    }


    /**
     * Lock the bulk init for shopee order.
     * $time in seconds
     * 
     * @param $time 
     */
    private function setLockForShopeeOrderBulkInit($time, $auth_id) 
    {
        try {
            if ($time > 0) {
                Cache::put($this->lockShopeeOrderBulkInitKey($auth_id), "locked", Carbon::now()->addSeconds($time));
            }
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Remove lock the bulk init for shopee order.
     * $time in seconds
     * 
     * @param $time 
     */
    private function removeLockForShopeeOrderBulkInit($auth_id) 
    {
        try {
            if (Cache::has($this->lockShopeeOrderBulkInitKey($auth_id))) {
                Cache::forget($this->lockShopeeOrderBulkInitKey($auth_id));
            }
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Lock the bulk init for shopee order.
     * $time in seconds
     * 
     * @param $time 
     */
    private function isLockedShopeeOrderBulkInit($auth_id) 
    {
        try {
            $cached_val = Cache::get($this->lockShopeeOrderBulkInitKey($auth_id));
            if (isset($cached_val) and $cached_val == "locked") {
                return true;
            }
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


    private function getKeyForLastShopeeOrderIdForBulkInitLock($auth_id) 
    {
        return 'lock_shopee_order_bulk_init__'.$auth_id.'__last_ordersn';
    }


    private function setLastShopeeOrderIdForBulkInitLock($ordersn, $auth_id)
    {
        if (Cache::has($this->getKeyForLastShopeeOrderIdForBulkInitLock($auth_id))) {
            Cache::forget($this->getKeyForLastShopeeOrderIdForBulkInitLock($auth_id));
        }
        Cache::put($this->getKeyForLastShopeeOrderIdForBulkInitLock($auth_id), $ordersn, Carbon::now()->addMinutes(10));
    }


    private function getLastShopeeOrderIdForBulkInitLock($auth_id)
    {
        if (Cache::has($this->getKeyForLastShopeeOrderIdForBulkInitLock($auth_id))) {
            return Cache::get($this->getKeyForLastShopeeOrderIdForBulkInitLock($auth_id));
        }
        return "";   
    }


    private function removetLastShopeeOrderIdForBulkInitLock($auth_id)
    {
        if (Cache::has($this->getKeyForLastShopeeOrderIdForBulkInitLock($auth_id))) {
            Cache::forget($this->getKeyForLastShopeeOrderIdForBulkInitLock($auth_id));
        }
    }


    private function matchLastLastShopeeOrderIdForBulkInitLock($ordersn, $auth_id)
    {
        $cached_ordersn = $this->getLastShopeeOrderIdForBulkInitLock($auth_id);
        if (isset($cached_ordersn) and !empty($cached_ordersn) and $cached_ordersn == $ordersn) {
            return true;
        }
        return false;
    }
}