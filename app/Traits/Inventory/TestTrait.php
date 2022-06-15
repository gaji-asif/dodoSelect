<?php

namespace App\Traits\Inventory;

use App\Models\InventoryProductsStockUpdate;
use App\Models\Lazada;
use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use App\Models\WooShop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait TestTrait
{
    use ShopeeInventoryProductsStockUpdateTrait;

    public function testing()
    {
       /**
        * Testing - start
        *
        * # order_id = 22052422A7P3AV
        *
        * Only 1 item found
        * # dodo_product_id = 6
        */
        $order_id = "220530HBW6JF30";
        $obj = ShopeeOrderPurchase::whereOrderId($order_id)->first();
        if (isset($obj)) {
            InventoryProductsStockUpdate::whereOrderId($order_id)->delete();
            $line_items = json_decode($obj->line_items);
            $this->initInventoryQtyUpdateForShopee($obj);
        }
 
 
 
       // /**
       //  * Testing - start
       //  *
       //  * # order_id = 545724777213366
       //  *
       //  * Only 1 item found
       //  * # dodo_product_id = 6
       //  */
       // $obj = \App\Models\LazadaOrderPurchase::whereOrderId("545724777213366")->first();
       // if (isset($obj)) {
       //     InventoryProductsStockUpdate::whereOrderId("545724777213366")->delete();
       //     $this->initInventoryQtyUpdateForLazada($obj);
       // }
 
      
       Log::debug("done");
       /**
        * Testing - end
        */
    }
}