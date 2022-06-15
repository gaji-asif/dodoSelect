<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WooOrderPurchaseDetail extends Model
{
    use HasFactory;

    public function product()
    {
        return $this->belongsTo(WooProduct::class,'product_id', 'id')->with('getQuantity');
    }
    public function orderPurchase()
    {
        return $this->belongsTo(WooOrderPurchase::class,'order_purchase_id', 'id');
    }
  
}
