<?php
 
namespace App\Traits\Inventory;

use Illuminate\Support\Facades\Log;

trait JobTypeTrait
{
    /* Used to determine which job is used for invetnoryqty update. */
    public $inventory_update_job = "inventory_update_job";
    public $stock_adjust_job = "stock_adjust_job"; 
    public $stock_handle_reversed_qty_job = "stock_handle_reversed_qty_job"; 

    /* Param to passed for "stock_adjust_job" */
    public $products_arr = [];


    public function getUsedJobForProductStockUpdate() 
    {
        return $this->stock_handle_reversed_qty_job;
    }


    public function getProductsArrForStockAdjustment() 
    {
        return $this->products_arr;
    }


    public function resetProductsArrForStockAdjustment() 
    {
        return $this->products_arr = [];
    }


    /**
     * @param object $product
     * @param integer $qty
     */
    public function setProductsArrForStockAdjustment($product, $qty) 
    {
        if (isset($product, $product->dodo_product_id) and $qty >= 0) {
            /* Set "dodo_product_id". */
            if (!array_key_exists($product->dodo_product_id, $this->products_arr)) {
                $this->products_arr[$product->dodo_product_id]["dodo_product_id"] = $product->dodo_product_id;
            }
            
            /* Set "qty" to be adjusted. */
            if (!isset($this->products_arr[$product->dodo_product_id]["qty"])) {
                $this->products_arr[$product->dodo_product_id]["qty"] = $qty;
            } else {
                $this->products_arr[$product->dodo_product_id]["qty"] += $qty;
            }

            /* Set "seller_id". */
            if (isset($product->seller_id)) {
                $this->products_arr[$product->dodo_product_id]["seller_id"] = $product->seller_id;
            }
        }
    }
}