<?php

namespace App\Traits\Inventory;

use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isJson;

trait WooCommerceInventoryProductsStockUpdateTrait
{
    use InventoryProductsStockUpdateTrait;

    /**
     * Init inventory quantity update.
     * 
     * @param object $order_purchase
     * @param string $type
     */
    public function initInventoryQtyUpdateForWooCommerce($order_purchase, $type="remove") 
    {
        try {
            if (!in_array($type, ["add", "remove"])) {
                return;
            }
            
            $items = $this->getItemsFromWooCommerceOrder($order_purchase);
            if (sizeof($items) > 0) {
                foreach ($items as $index => $item) {
                    if ($index == 0) {
                        $this->createNewEntryForHandledProductsInventoryForSpecificOrder($order_purchase->order_id, $order_purchase->status, $order_purchase->website_id, $this->getTagForWooCommercePlatform());
                    }

                    $qty = 1;
                    if ($item->quantity) {
                        $qty = (int) $item->quantity;
                    }
                    
                    $product = null;
                    
                    /* For "variable" type products. */
                    if (isset($item->variation_id)) {
                        $product = $this->getPlatormSpecificDodoProduct($item->variation_id, $order_purchase->website_id, $this->getTagForWooCommercePlatform());
                    }

                    /* For "simple" type products. */
                    if (!isset($product) and isset($item->product_id)) {
                        $product = $this->getPlatormSpecificDodoProduct($item->product_id, $order_purchase->website_id, $this->getTagForWooCommercePlatform());
                    }

                    /* Get woo commerce prdouct based on sku */
                    if (!isset($product) and isset($item->sku) and !empty($item->sku)) {
                        $product = $this->getPlatormSpecificDodoProductBasedOnSku($item->item_sku, $order_purchase->website_id, $this->getTagForWooCommercePlatform());
                    } 

                    if (isset($product, $product->dodo_product_id) and !empty($product->dodo_product_id)) {
                        if ($this->getUsedJobForProductStockUpdate() == $this->inventory_update_job) {
                            if ($this->initQtyUpdateJob($product, $qty, $type)) {
                                continue;
                            }
                        } else if ($this->getUsedJobForProductStockUpdate() == $this->stock_handle_reversed_qty_job) {
                            $this->saveLogForReservedQty($order_purchase->order_id, $order_purchase->website_id, $this->getTagForWooCommercePlatform(), $product->dodo_product_id, $qty, $order_purchase->seller_id);
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
     * Retrieve items from order.
     * 
     * @param object $order_purchase
     * @return array
     */
    private function getItemsFromWooCommerceOrder($order_purchase)
    {
        if (isset($order_purchase->line_items) and !empty($order_purchase->line_items) and isJson($order_purchase->line_items)) {
            return json_decode($order_purchase->line_items);
        }
        return [];
    }
}