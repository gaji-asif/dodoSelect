<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WooOrder extends Model
{
    use HasFactory;

    protected $table = 'woo_orders';
    protected $guarded = ['id'];

    protected $casts = [
    	'date' => 'datetime',
    	'time' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Model\User::class, 'shop_id', 'shop_id');
    }
}
