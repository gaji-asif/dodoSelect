<?php
 
namespace App\Traits\Inventory;
 
use App\Models\LazadaProduct;
use App\Models\ShopeeProduct;
use App\Models\WooProduct;
use Illuminate\Support\Facades\Log;
 
trait ProductTrait
{
   use PlatformTrait;
 
   /**
    * Get dodo_product_id for a product based on platform.
    *
    * @param string $order_id
    * @param string $shop_id
    * @param string $for
    * @return object|null
    */
   private function getDodoProductIdFromDatabase($product_id, $shop_id, $for="")
   {
       try {
           if (!empty($for)) {
               $for = strtolower($for);
 
               if (in_array($for, [
                   $this->getTagForShopeePlatform(),
                   $this->getTagForLazadaPlatform(),
                   $this->getTagForWooCommercePlatform()
               ])) {
                   $product = $this->getPlatormSpecificDodoProduct($product_id, $shop_id, $for);
 
                   if (isset($product, $product->dodo_product_id)) {
                       return $product->dodo_product_id;
                   }
               }
           }
       } catch (\Exception $exception) {
           Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
       }
 
       return "";
   }
 
 
   /**
    * Get dodo_product for a product based on platform.
    *
    * @param string $order_id
    * @param string $shop_id
    * @param string $for
    * @return object|null
    */
   private function getPlatormSpecificDodoProduct($product_id, $shop_id, $for)
   {
       $product = null;
       try {
           if (in_array($for, [
               $this->getTagForShopeePlatform(),
               $this->getTagForLazadaPlatform(),
               $this->getTagForWooCommercePlatform()
           ])) {
               if ($for == $this->getTagForShopeePlatform()) {
                   $product = ShopeeProduct::whereProductId($product_id)->whereWebsiteId($shop_id)->first();
               } else if ($for == $this->getTagForLazadaPlatform()) {
                   $product = LazadaProduct::whereProductId($product_id)->whereWebsiteId($shop_id)->first();
               } else if ($for == $this->getTagForWooCommercePlatform()) {
                   $product = WooProduct::whereProductId($product_id)->whereWebsiteId($shop_id)->first();
               }
           }
       } catch (\Exception $exception) {
           Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
       }
       return $product;
   }
 
 
   /**
    * Get dodo_product for a product based on platform.
    *
    * @param string $order_id
    * @param string $shop_id
    * @param string $for
    * @return object|null
    */
   private function getPlatormSpecificDodoProductBasedOnSku($sku, $shop_id, $for)
   {
       $product = null;
       try {
           if (in_array($for, [
               $this->getTagForShopeePlatform(),
               $this->getTagForLazadaPlatform(),
               $this->getTagForWooCommercePlatform()
           ])) {
               if ($for == $this->getTagForShopeePlatform()) {
                   $product = ShopeeProduct::whereProductCode($sku)->whereWebsiteId($shop_id)->first();
               } else if ($for == $this->getTagForLazadaPlatform()) {
                   $product = LazadaProduct::whereProductCode($sku)->whereWebsiteId($shop_id)->first();
               } else if ($for == $this->getTagForWooCommercePlatform()) {
                   $product = WooProduct::whereProductCode($sku)->whereWebsiteId($shop_id)->first();
               }
           }
       } catch (\Exception $exception) {
           Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
       }
       return $product;
   }
}
