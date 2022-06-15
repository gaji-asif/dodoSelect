<?php

namespace App\Traits;

use App\Models\ShopeeProductBoost;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait ShopeeProductBoostTrait
{
    use ShopeeTrait;

    /** 
     * Limit for products to be boosted at a time for a shop. 
     */
    private function getBoostedProductLimit() {
        return 5;
    }


    /**
     * Delete old boosted products.
     * If "repeat_boost" is true for any of the expired products then those are added to queue again.
     * 
     * @param integer $shopee_shop_id
     */
    private function deleteOldBoostingProducts($shopee_shop_id) 
    {
        try {
            $shopee_boosting_products = ShopeeProductBoost::whereStatus("boosting")
                ->whereWebsiteId($shopee_shop_id)
                ->where("boost_expires_at", "<=", Carbon::now()->format("Y-m-d H:i:s"))
                ->get();
            
            if (sizeof($shopee_boosting_products) > 0) {
                foreach ($shopee_boosting_products as $old_product) {
                    if ($old_product["repeat_boost"]) {
                        $this->addNewProductInQueueForBoostingInShopee($old_product["item_id"], $old_product["website_id"]);
                    }
                    $old_product->delete();
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get boosted products from "Shopee" by api.
     * 
     * @param integer $shopee_shop_id
     * @return array
     */
    private function fetchBoostedProductsFromShopeeApi($shopee_shop_id)
    {
        try {
            $client = $this->getShopeeClient((int) $shopee_shop_id);
            if (isset($client)) {
                $response = $client->item->getBoostedItems()->getData();
                if (isset($response["request_id"], $response["items"])) {
                    return $response["items"];
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Get boosted products from "Shopee" by api.
     * 
     * @param integer $shopee_shop_id
     * @param array $item_ids
     * @return array
     */
    private function setBoostedProductsByShopeeApi($shopee_shop_id, $item_ids)
    {
        try {
            $client = $this->getShopeeClient((int) $shopee_shop_id);
            if (isset($client)) {
                $response = $client->item->boostItem([
                    'item_id' => $item_ids
                ])->getData();
                if (isset($response["request_id"])) {
                    if (isset($response["batch_result"])) {
                        return $response["batch_result"];
                    } else if (isset($response["error"])) {
                        return $response;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Delete old boosted products.
     * 
     * @param integer $product_id
     * @param integer $shopee_shop_id
     */
    private function deleteSpecificOldBoostingProductFromDatabase($product_id, $shopee_shop_id) 
    {
        try {
            $queued_product = ShopeeProductBoost::whereStatus("boosting")
                ->whereItemId($product_id)
                ->whereWebsiteId($shopee_shop_id)
                ->where("boost_expires_at", "<=", Carbon::now()->format("Y-m-d H:i:s"))
                ->first();
            if (isset($queued_product)) {
                if ($queued_product->repeat_boost) {
                    $this->addNewProductInQueueForBoostingInShopee($product_id, $shopee_shop_id);
                }
                $queued_product->delete();
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get queued product ids("product_id") waiting for boosting.
     * 
     * @param integer $shopee_shop_id
     */
    private function getQueueProductForBoosting($shopee_shop_id)
    {
        try {
            $queued_products = ShopeeProductBoost::whereStatus('queued')
                ->whereWebsiteId($shopee_shop_id)
                ->get();
                
            $queues_product_ids = [];
            if (sizeof($queued_products) > 0) {
                foreach ($queued_products as $product) {
                    array_push($queues_product_ids, $product->item_id);
                }
            }
            return $queues_product_ids;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Get queued product ids("product_id") are boosting now.
     * 
     * @param integer $shopee_shop_id
     */
    private function getQueueProductsBoostingNow($shopee_shop_id)
    {
        try {
            $queued_products = ShopeeProductBoost::whereStatus('boosting')
                ->whereWebsiteId($shopee_shop_id)
                ->where("boost_expires_at", ">", Carbon::now()->format("Y-m-d H:i:s"))
                ->get();
                
            $queues_product_ids = [];
            if (sizeof($queued_products) > 0) {
                foreach ($queued_products as $product) {
                    array_push($queues_product_ids, $product->item_id);
                }
            }
            return $queues_product_ids;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Add new product for boosting.
     * 
     * @param integer $item_id
     * @param integer $shopee_shop_id
     * @param boolean $repeat_boost
     */
    private function addNewProductInQueueForBoostingInShopee($item_id, $shopee_shop_id, $repeat_boost=true)
    {
        try {
            $queue_product = new ShopeeProductBoost();
            $queue_product->item_id = (int) $item_id;
            $queue_product->website_id = (int) $shopee_shop_id;
            $queue_product->repeat_boost = $repeat_boost;
            $queue_product->save();
         } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Check if there are already 5(limit) products are "boosting" right now in Shopee.
     * If not check for products which are "queued" and if there are then initiate those products for boosting.
     * 
     * @param integer $shopee_shop_id
     * @return boolean
     */
    private function checkQueueForEmptySlotForBoostedProducts($shopee_shop_id) 
    {
        try {
            $total_queued_products_count = sizeof($this->getQueueProductForBoosting($shopee_shop_id));
            if($total_queued_products_count > 0) {
                $total_boosting_products_count = sizeof($this->getQueueProductsBoostingNow($shopee_shop_id));
                if ($total_boosting_products_count < $this->getBoostedProductLimit()) {
                    return true;
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


    /**
     * Get html for repeat button for boosting/queued products.
     * If "repeat_boost" is true, then return button for cancelling repeat.
     * If fasle, then return button for enabling repeat.
     * 
     * @param integer $item_id
     * @param boolean $in_repeat
     * @return string $html
     */
    private function getRepeatBoostingSameProductHtml($item_id, $in_repeat = false) 
    {
        if ($in_repeat) {
            return '<div>
                <button type="button" class="btn-action--red text-xs mt-1 stop_repeat_btn stop_repeat_'.$item_id.'_btn" data-product_id="'.$item_id.'" title="Stop Repeating Boost">
                    <i class="bi bi-arrow-repeat text-base"></i>
                </button>
            </div>';
        } else {
            return '<div>
                <button type="button" class="btn-action--green text-xs mt-1 init_repeat_btn init_repeat_'.$item_id.'_btn" data-product_id="'.$item_id.'" title="Initiate Repeating Boost">
                    <i class="bi bi-arrow-repeat text-base"></i>
                </button>
            </div>';
        }
    }
}