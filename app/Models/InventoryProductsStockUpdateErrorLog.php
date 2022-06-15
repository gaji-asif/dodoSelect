<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryProductsStockUpdateErrorLog extends Model
{
    use HasFactory;

    /**
     * Relationship to `products` table
     *
     * @return mixed
     */
    public function dodoProduct()
    {
        return $this->belongsTo(Product::class, 'dodo_product_id', 'id');
    }


    /**
     * Query to search by from datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     */
    public function scopeSearchDatatable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where('product_name', 'like', "%$keyword%")
                ->orWhere('platform_name', 'like', "%$keyword%")
                ->orWhere('shop_name', 'like', "%$keyword%")
                ->orWhere('message', 'like', "%$keyword%");
        }
        return;
    }
}
