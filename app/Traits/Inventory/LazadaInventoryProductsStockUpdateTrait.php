<?php
 
namespace App\Traits\Inventory;
 
use App\Models\LazadaOrderPurchaseItem;
use App\Models\LazadaProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
 
use function PHPUnit\Framework\isJson;
 
trait LazadaInventoryProductsStockUpdateTrait
{
   use InventoryProductsStockUpdateTrait;
 
   /**
    * Init inventory quantity update.
    *
    * @param object $order_purchase
    * @param string $type
    */
    public function initInventoryQtyUpdateForLazada($order_purchase, $type="remove")
    {
        try {
            if (!in_array($type, ["add", "remove"])) {
                return;
            }

            $skus = $this->getProductSkusForLazadaOrderFromDatabase($order_purchase);
 
            if (sizeof($skus) > 0) {
                foreach ($skus as $index => $sku) {
                    if ($index == 0) {
                        $this->createNewEntryForHandledProductsInventoryForSpecificOrder($order_purchase->order_id, $order_purchase->status_custom, $order_purchase->website_id, $this->getTagForLazadaPlatform());
                    }

                    $qty = 1;
                  
                    $lazada_product = $this->getLazadaProductFromDatabase($order_purchase->website_id, $sku);
                    if (!isset($lazada_product)) {
                        continue;
                    }

                    /* If product found for "dodo_product_id" then direct execute the job to update inventory. */
                    if (isset($lazada_product->dodo_product_id)) {
                        $dodo_product = Product::find($lazada_product->dodo_product_id);
                    }
 
                    if ($this->getUsedJobForProductStockUpdate() == $this->inventory_update_job) {
                        if (isset($dodo_product)) {
                            $this->initInventoryUpdateJobs($dodo_product);
                            continue;
                        }
    
                        /* SKU(product_code) based inventory update. */
                        if (isset($lazada_product->product_code) and !empty($lazada_product->product_code)) {
                            $this->initUpdateInventoryForSpecificProductSku($lazada_product->product_code, $order_purchase->website_id, $this->getTagForLazadaPlatform());
                            continue;
                        }
    
                        if (isset($lazada_product->type) and !empty($lazada_product->type) and $lazada_product->type == "variable") {
                            /* For "variable" type products. */
                            if (isset($lazada_product->parent_id)) {
                                /**
                                 * NOTE:
                                 * Sometimes no such data is found in database for "parent_id" based lazada product search.
                                 */
                                $this->initUpdateInventoryForSpecificProduct($lazada_product->parent_id, $order_purchase->website_id, $this->getTagForLazadaPlatform());
                            }
                        } else {
                            /* For "simple" type products. */
                            if (isset($lazada_product->product_id)) {
                                $this->initUpdateInventoryForSpecificProduct($lazada_product->product_id, $order_purchase->website_id, $this->getTagForLazadaPlatform());
                            }
                        }
                    } else if ($this->getUsedJobForProductStockUpdate() == $this->stock_handle_reversed_qty_job) {
                        if (!isset($dodo_product)) {
                            $dodo_product = $this->getDodoProductFromDataFoundInOrderPurchase($lazada_product, $order_purchase);
                        }
                        if (isset($dodo_product)) {
                            $this->saveLogForReservedQty($order_purchase->order_id, $order_purchase->website_id, $this->getTagForLazadaPlatform(), $dodo_product->id, $qty, $order_purchase->seller_id);
                            $this->updateReversedQtyForInventoryManagement($dodo_product->id, $qty);
                            $this->setProductsArrForStockAdjustment($dodo_product, $qty);
                        }
                    } else {
                        if (!isset($dodo_product)) {
                            $dodo_product = $this->getDodoProductFromDataFoundInOrderPurchase($lazada_product, $order_purchase);
                        }
                        if (isset($dodo_product)) {
                            /* Without assinging "dodo_product_id" the product won't be added in the array */
                            $dodo_product->dodo_product_id = $dodo_product->id;
                            $this->setProductsArrForStockAdjustment($dodo_product, $qty);
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
    private function getProductSkusForLazadaOrderFromDatabase($order_purchase)
    {
        $skus = [];
        $item_ids = [];
        if (isset($order_purchase->order_item_ids) and !empty($order_purchase->order_item_ids) and isJson($order_purchase->order_item_ids)) {
            $item_ids = json_decode($order_purchase->order_item_ids);
        }
        if (sizeof($item_ids) > 0) {
            $order_items = $this->getOrderItemsFromDatabase($order_purchase, $item_ids);
            foreach ($order_items as $item) {
                if (isset($item->sku) and !empty($item->sku)) {
                    array_push($skus, $item->sku);
                }
            }
        }
        return $skus;
    }
 
 
    private function getOrderItemsFromDatabase($order_purchase, $item_ids)
    {
        return LazadaOrderPurchaseItem::select('order_item_id', 'website_id', 'sku', 'order_id')
            ->whereIn('order_item_id', $item_ids)
            ->whereOrderId($order_purchase->order_id)
            ->whereWebsiteId($order_purchase->website_id)
            ->get();
    }
 
 
    private function getLazadaProductFromDatabase($website_id, $sku)
    {
        return LazadaProduct::whereWebsiteId($website_id)
            ->select('dodo_product_id', 'website_id', 'product_code', 'type', 'parent_id')
            ->whereProductCode($sku)
            ->first();
    }


    /**
     * If dodo product from database using the product related information found in order_purchase.
     * 
     * @param object $lazada_product
     * @param object $order_purchase
     * @return object|null
     */
    private function getDodoProductFromDataFoundInOrderPurchase($lazada_product, $order_purchase)
    {
        $dodo_product = null;
        try {
            $product = $this->getLazadaProductIfDirectlyDodoProductNotFound($lazada_product, $order_purchase);
            if (isset($product)) {
                if (isset($product->dodo_product_id) and !empty($product->dodo_product_id)) {
                    $dodo_product = Product::find($product->dodo_product_id);
                } 
                if (!isset($dodo_product) and isset($product->product_code) and !empty($product->product_code)) {
                    $dodo_product = Product::whereProductCode($product->product_code)->first();
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return $dodo_product;
    }


    /**
     * If fail to get dodo product directly then use data from order to get the lazada product from database.
     * 
     * @param object $lazada_product
     * @param object $order_purchase
     * @return object|null
     */
    private function getLazadaProductIfDirectlyDodoProductNotFound($lazada_product, $order_purchase)
    {
        $product = null;
        try {
            /* SKU(product_code) based inventory update. */
            if (isset($lazada_product->product_code) and !empty($lazada_product->product_code)) {
                $product = $this->getPlatormSpecificDodoProductBasedOnSku($lazada_product->product_code, $order_purchase->website_id, $this->getTagForLazadaPlatform());
            }
                
            if (!isset($product) || !isset($product->dodo_product_id)) {
                if (isset($lazada_product->parent_id)) {
                    /**
                     * NOTE:
                     * Sometimes no such data is found in database for "parent_id" based lazada product search.
                     */
                    $product = $this->getPlatormSpecificDodoProduct($lazada_product->parent_id, $order_purchase->website_id, $this->getTagForLazadaPlatform());
                }
            }

            if (!isset($product) || !isset($product->dodo_product_id)) {
                /* For "simple" type products. */
                if (isset($lazada_product->product_id)) {
                    $product = $this->getPlatormSpecificDodoProduct($lazada_product->product_id, $order_purchase->website_id, $this->getTagForLazadaPlatform());
                }
            }  
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return $product;
    }
}
