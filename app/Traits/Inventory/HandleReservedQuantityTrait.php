<?php

namespace App\Traits\Inventory;

use App\Models\InventoryProductsReservedQuantityLog;
use App\Models\ProductMainStock;
use App\Jobs\InventoryQtySync;
use App\Jobs\InventoryQtySyncShopee;
use App\Models\Product;
use App\Models\ShopeeProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait HandleReservedQuantityTrait
{
    use ShopTrait;

    public function saveLogForReservedQty($order_id, $website_id, $platform, $dodo_product_id, $quantity, $status="processing", $seller_id=0)
    { 
        $obj = new InventoryProductsReservedQuantityLog();
        $obj->order_id = $order_id;
        $obj->dodo_product_id = $dodo_product_id;
        $obj->quantity = $quantity;
        $obj->website_id = $website_id;
        $obj->platform = $platform;
        $obj->status = (isset($status) and !empty($status) and in_array(strtolower($status), ["processing", "processed"]))?strtolower($status):"processing";
        $shop_info = $this->getShopInfoByPlatformAndId($platform, $website_id, $seller_id);
        if (isset($shop_info, $shop_info->shop_name)) {
            $obj->shop_name = $shop_info->shop_name;
        }
        $obj->save();
    }


    public function updateReversedQtyForInventoryManagement($dodo_product_id, $qty, $type="add", $adjust_reserved_qty=true)
    {
        $obj = ProductMainStock::whereProductId($dodo_product_id)->first();
        if (isset($obj)) {
            if ($type == "add") {
                if ($adjust_reserved_qty) {
                    $obj->reserved_quantity += $qty;
                    $obj->display_reserved_qty += $qty;
                    $new_qty = $obj->quantity - $qty;
                    $obj->quantity = $new_qty<0?0:$new_qty;
                } else {
                    $new_qty = $obj->quantity + $qty;
                    $obj->quantity = $new_qty<0?0:$new_qty;
                }
                $obj->save();
            } else if ($type == "remove") {
                $new_reserved_qty = $obj->reserved_quantity - $qty;
                $obj->reserved_quantity = $new_reserved_qty<0?0:$new_reserved_qty;
                $obj->save();
            }
        }
    }


    /**
     * @param iterable|object $product_id
     * @param array $adjust_stock
     * @param integer $check
     * @param integer|null $seller_id
     * @param integer|null $staff_id
     * 
     * NOTE:
     * $check 0 means remove from stock and $check 1 means add to stock.
     * Add =>
     * {"check":"1","product_id":["231"],"adjust_stock":["3"]}
     * Remove =>
     * {"check":"0","product_id":["231"],"adjust_stock":["5"]}
     */
    public function handleMarketPlaceAvailableQty($product_id, $adjust_stock, $check, $seller_id = null, $staff_id = null)
    {
        try {
            if(isset($product_id) && isset($adjust_stock)) {
                /* $product_id contains "dodo_product_id". */
                foreach($product_id as $key => $item) {
                    $product[$key][0] = $item;
                }

                /* $adjust_stock contains quantity to be adjusted for specific dodo_product. */
                foreach($adjust_stock as $key => $item) {
                    $product[$key][1] = $item;
                }

                if ($check == 1) {
                    foreach($product as $item) {
                        $dodoProduct = Product::find($item[0]);
                        InventoryQtySync::dispatch($dodoProduct, null, true);
                        sleep(1);

                        if ($dodoProduct->child_products) {
                            $child_sku = explode(",", $dodoProduct->child_products);
                            foreach ($child_sku as $index => $child) {
                                if ($dodoProduct->child_products) {
                                    $child_sku = explode(",", $dodoProduct->child_products);
                                    foreach ($child_sku as $index => $child) {
                                        $dodoChildProduct = Product::query()
                                            ->where('product_code', trim($child))
                                            ->with('getQuantity')
                                            ->firstOrFail();
                                        sleep(1);
                                        InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity, true)
                                            ->delay(Carbon::now()->addSeconds($index*5));
                                    }
                                }
                            }
                        }
                    }
                }
                else {
                    foreach($product as $item) {
                        $dodoProduct = Product::find($item[0]);
                        sleep(1);
                        InventoryQtySync::dispatch($dodoProduct, null, true);

                        if ($dodoProduct->child_products) {
                            $child_sku = explode(",", $dodoProduct->child_products);
                            foreach ($child_sku as $index => $child) {
                                $dodoChildProduct = Product::query()
                                    ->where('product_code', trim($child))
                                    ->with('getQuantity')
                                    ->firstOrFail();
                                sleep(1);
                                InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity, true)
                                    ->delay(Carbon::now()->addSeconds($index*5));
                            }
                        }
                    }
                }
                return true;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Initate the inventory sync job.
     *
     * @param array $products
     * @param string $type
     */
    public function initInventoryHandleMarketPlaceAvailableQty($products, $type="remove")
    {
        try {
            $product_id = [];
            $adjust_stock = [];
            $seller_id = null;

            foreach ($products as $product) {  
                array_push($product_id, $product["dodo_product_id"]);
                array_push($adjust_stock, $product["qty"]);
                if (!isset($seller_id) and isset($product["seller_id"])) {
                    $seller_id = $product["seller_id"];
                }
            }

            if (sizeof($product_id) > 0) {
                $this->handleMarketPlaceAvailableQty($product_id, $adjust_stock, $this->getCheckValue($type), $seller_id);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}