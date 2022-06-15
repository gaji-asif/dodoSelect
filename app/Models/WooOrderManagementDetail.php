<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WooOrderManagementDetail extends Model
{
    use HasFactory;

    public function product()
    {
        return $this->belongsTo(WooProduct::class,'product_id', 'id')->with('getQuantity');
    }
}
