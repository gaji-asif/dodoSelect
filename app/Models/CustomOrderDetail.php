<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomOrderDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Relationship to `custom_orders` table
     *
     * @return mixed
     */
    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class)->withDefault();
    }

    /**
     * Relationship to `custom_order_product_images` table
     *
     * @return mixed
     */
    public function customOrderProductImages()
    {
        return $this->hasMany(CustomOrderProductImage::class);
    }
}
