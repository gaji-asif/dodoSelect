<?php
 
namespace App\Traits\Inventory;
 
use App\Jobs\InventoryQtySync;
use App\Models\InventoryProductsStockUpdate;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
 
trait InventoryProductsStockUpdateTrait
{
    use ProductTrait, AdjustInventoryStockTrait, JobTypeTrait, HandleReservedQuantityTrait;
 
   /**
    * Create new entry only if not found in table.
    *
    * @param string $order_id
    * @param string $shop_id
    * @param string $custom_status
    * @param string $for
    */
    public function createNewEntryForHandledProductsInventoryForSpecificOrder($order_id, $custom_status, $shop_id, $for="")
    {
        if (!empty($for)) {
            $for = strtolower($for);
            if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($order_id, $shop_id, $for)) {
                $obj = new InventoryProductsStockUpdate();
                $obj->order_id = $order_id;
                $obj->custom_status = $custom_status;
                $obj->platform = $this->getPlatformNo(strtolower($for));
                $obj->platform_sid = $shop_id;
                $obj->save();
            }
        }
    }
 
 
   /**
    * Check if the products under the given $order_id has been already handled to update inventory.
    * NOTE:
    * Returning "false" means no such entry found.
    *
    * @param string $order_id
    * @param string $shop_id
    * @param string $for
    * @return boolean
    */
    public function checkIfInventoryUpdatedForProductsInSpecificOrder($order_id, $shop_id, $for="")
    {
        try {
            $obj = $this->getItemFromDatabaseForInventoryUpdatedProducts($order_id, $shop_id, $for);
            if (isset($obj)) {
                return true;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }
 
 
   /**
    * Get the object macting the $order_id from database.
    *
    * @param string $order_id
    * @param string $for
    * @return object|null
    */
    public function getItemFromDatabaseForInventoryUpdatedProducts($order_id, $shop_id, $for="")
    {
        try {
            if (!empty($for)) {
                $for = strtolower($for);
  
                if (in_array($for, [
                    $this->getTagForShopeePlatform(),
                    $this->getTagForLazadaPlatform(),
                    $this->getTagForWooCommercePlatform()
                ])) {
                    return InventoryProductsStockUpdate::whereOrderId($order_id)
                        ->wherePlatform($this->getPlatformNo($for))
                        ->wherePlatformSid($shop_id)
                        ->first();
                }
            } 
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }
 
 
   /**
    * Update inventory quantiry for specific product for specific platform.
    *
    * @param string $order_id
    * @param string $shop_id
    * @param string $for
    * @return object|null
    */
    public function initUpdateInventoryForSpecificProduct($product_id, $shop_id, $for="")
    {
        try {
            if (empty($for)) {
                return;
            }
 
            $product = $this->getPlatormSpecificDodoProduct($product_id, $shop_id, $for);
            if (isset($product)) {
                if (isset($product->dodo_product_id) and !empty($product->dodo_product_id)) {
                    $this->initInventoryUpdateJobs(Product::find($product->dodo_product_id));
                } else if (isset($product->product_code) and !empty($product->product_code)) {
                    $this->initInventoryUpdateJobs(Product::whereProductCode($product->product_code)->first());
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        } 
    }
 
 
   /**
    * Update inventory quantiry for specific product for specific platform.
    *
    * @param string $order_id
    * @param string $shop_id
    * @param string $for
    * @return object|null
    */
    public function initUpdateInventoryForSpecificProductSku($product_code, $shop_id, $for="")
    {
        try {
            if (empty($for)) {
                return;
            }
 
            $product = $this->getPlatormSpecificDodoProductBasedOnSku($product_code, $shop_id, $for);
            if (isset($product)) {
                if (isset($product->dodo_product_id) and !empty($product->dodo_product_id)) {
                    $this->initInventoryUpdateJobs(Product::find($product->dodo_product_id));
                } else if (isset($product->product_code) and !empty($product->product_code)) {
                    $this->initInventoryUpdateJobs(Product::whereProductCode($product->product_code)->first());
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
 
 
    /**
     * Initate the inventory sync job.
     *
     * @param object $dodo_product
     */
    private function initInventoryUpdateJobs($dodo_product, $quantity=0, $type="remove")
    {
       try {
            if (isset($dodo_product)) {
                $in_stock = $dodo_product->getQuantity->quantity;
                $qty = $in_stock;
                if ($quantity > 0) {
                    if ($type == "add") {
                        $qty += $quantity;
                    } else {
                        $qty -= $quantity;
                    }
                } else {
                    if ($type == "add") {
                        $qty += 1;
                    } else {
                        $qty -= 1;
                    }
                }
                if ($qty < 0) {
                    $qty = 0;
                }
                InventoryQtySync::dispatch($dodo_product, $qty);
                // InventoryQtySync::dispatch($dodo_product);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Dispatch job qty update in inventory.
     * 
     * @param object $product
     * @return boolean
     */
    private function initQtyUpdateJob($product, $qty=0, $type="remove")
    {
        if (isset($product, $product->dodo_product_id) and !empty($product->dodo_product_id) and in_array($type, ["add", "remove"]) and $qty >= 0) {
            $this->initInventoryUpdateJobs(Product::find((int) $product->dodo_product_id), $qty, $type);
            return true;
        }
        return false;
    }
}
