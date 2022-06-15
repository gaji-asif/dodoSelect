<?php

namespace App\Traits\Inventory;

use App\Models\Product;
use App\Models\Shopee;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isJson;

trait ShopeeInventoryProductsStockUpdateTrait
{
    use InventoryProductsStockUpdateTrait;

    /**
     * Init inventory quantity update.
     * 
     * @param object $order_purchase
     * @param string $type
     */
    public function initInventoryQtyUpdateForShopee($order_purchase, $type="remove") 
    {
        try {
            if (!in_array($type, ["add", "remove"])) {
                return;
            }
            
            $items = $this->getItemsFromShopeeOrder($order_purchase);
            if (sizeof($items) > 0) {
                foreach ($items as $index => $item) {
                    if ($index == 0) {
                        $this->createNewEntryForHandledProductsInventoryForSpecificOrder($order_purchase->order_id, $order_purchase->status_custom, $order_purchase->website_id, $this->getTagForShopeePlatform());
                    }

                    $qty = 1;
                    if (isset($item->variation_quantity_purchased) and !empty($item->variation_quantity_purchased)) {
                        $qty = (int) $item->variation_quantity_purchased;
                    }

                    $product = null;

                    $website_id = $this->getShopIdForShopee($order_purchase);
                    if ($website_id <= 0) {
                        return;
                    }

                    /** 
                     * For "variable" type products. 
                     * NOTE:
                     * "variation_id" is checked first because sometimes for "item_id", "dodo_product_id" might be missing.
                     */
                    if (isset($item->variation_id) and !empty($item->variation_id)) {
                        $product = $this->getPlatormSpecificDodoProduct($item->variation_id, $website_id, $this->getTagForShopeePlatform());
                    } 

                    if (!isset($product) and isset($item->variation_sku) and !empty($item->variation_sku)) {
                        $product = $this->getPlatormSpecificDodoProductBasedOnSku($item->variation_sku, $website_id, $this->getTagForShopeePlatform());
                    } 

                    /* For "simple" type products. */
                    if (!isset($product) and isset($item->item_id) and !empty($item->item_id)) {
                        $product = $this->getPlatormSpecificDodoProduct($item->item_id, $website_id, $this->getTagForShopeePlatform());
                    }

                    if (!isset($product) and isset($item->item_sku) and !empty($item->item_sku)) {
                        $product = $this->getPlatormSpecificDodoProductBasedOnSku($item->item_sku, $website_id, $this->getTagForShopeePlatform());
                    } 

                    if (isset($product, $product->dodo_product_id) and !empty($product->dodo_product_id)) {
                        if ($this->getUsedJobForProductStockUpdate() == $this->inventory_update_job) {
                            if ($this->initQtyUpdateJob($product, $qty, $type)) {
                                continue;
                            }
                        } else if ($this->getUsedJobForProductStockUpdate() == $this->stock_handle_reversed_qty_job) {
                            $this->saveLogForReservedQty($order_purchase->order_id, $order_purchase->website_id, $this->getTagForShopeePlatform(), $product->dodo_product_id, $qty, $order_purchase->seller_id);
                            $this->updateReversedQtyForInventoryManagement($product->dodo_product_id, $qty);
                            $this->setProductsArrForStockAdjustment($product, $qty);
                            continue;
                        } else {
                            $this->setProductsArrForStockAdjustment($product, $qty);
                            continue;
                        }
                    }
                }

                /* This is for when we adjusting stock qty but not updating the qty. */
                if ($this->getUsedJobForProductStockUpdate() == $this->stock_adjust_job) {
                    $this->initInventoryStockAdjustmentJobs($this->getProductsArrForStockAdjustment(), $type);
                    $this->resetProductsArrForStockAdjustment();
                }

                /* Update reserved_quantity */
                if ($this->getUsedJobForProductStockUpdate() == $this->stock_handle_reversed_qty_job) {
                    $this->initInventoryHandleMarketPlaceAvailableQty($this->getProductsArrForStockAdjustment(), $type);
                    $this->resetProductsArrForStockAdjustment();
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get shopee "shop_id".
     * 
     * NOTE:
     * The "website_id" found in $order_purchase does not match the "website_id" of "shopee_product".
     * "shop_id" of Shopee has been used as "website_id" for shopee products.s
     * 
     * @param object $order_purchase
     * @return integer
     */
    private function getShopIdForShopee($order_purchase)
    {
        $shop = Shopee::find((int) $order_purchase->website_id);
        if (!isset($shop, $shop->shop_id) || empty($shop->shop_id)) {
            return -1;
        }
        return (int) $shop->shop_id;
    }


    /**
     * Retrieve items from order.
     * 
     * @param object $order_purchase
     * @return array
     */
    private function getItemsFromShopeeOrder($order_purchase)
    {
        if (isset($order_purchase->line_items) and !empty($order_purchase->line_items) and isJson($order_purchase->line_items)) {
            return json_decode($order_purchase->line_items);
        }
        return [];
    }
}