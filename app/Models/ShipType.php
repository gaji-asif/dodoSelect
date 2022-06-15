<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipType extends Model
{
    use HasFactory;

    protected $table = 'ship_types';
    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];



    /**
     * Relationship to `order_managements` table
     *
     * @return mixed
     */
    public function orderProduct()
    {
        return $this->hasMany(OrderManagement::class,'shiptype_id','id');
    }

}
