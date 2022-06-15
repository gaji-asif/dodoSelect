<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeOrderPurchaseDetail extends Model
{
    use HasFactory;

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'shopee_order_purchase_details';

    public function product()
    {
        return $this->belongsTo(ShopeeProduct::class,'product_id', 'id')->with('getQuantity');
    }
    public function orderPurchase()
    {
        return $this->belongsTo(ShopeeOrderPurchase::class,'order_purchase_id', 'id');
    }
}
