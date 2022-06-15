<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WCOrderPurchase extends Model
{
    use HasFactory;


    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id', 'id');
    }
    public function orderProductDetails()
    {
        return $this->hasMany(OrderPurchaseDetail::class,'order_purchase_id','id')->with('product');
    }
}
