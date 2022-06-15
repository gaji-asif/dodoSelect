<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $table = 'exchange_rate';
    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'rate',
    ];

    public function orderProduct()
    {
        return $this->hasMany(OrderManagement::class,'exchange_rate_id','id');
    }
}
