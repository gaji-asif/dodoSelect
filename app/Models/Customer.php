<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Define `order_type` field value
     *
     * @var string
     */
    CONST ORDER_TYPE_DEFAULT = 1;
    CONST ORDER_TYPE_CUSTOM = 2;

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'customer_name',
        'contact_phone',
        'seller_id',
        'order_type'
    ];

    public function orderProduct()
    {
        return $this->hasMany(OrderManagement::class);
    }

    /**
     * Relationship to `users` table as a seller
     *
     * @return mixed
     */
    public function seller()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /**
     * Filter by `order_type` custom only
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustomOrder($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_CUSTOM);
    }

    /**
     * Query to check if customer is Dropshipper
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $customerType
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeCustomerType($query, $customerType)
    {
        if ($customerType != '0') {
            return $query->where('is_dropshipper', '=', 1);
        }

        return;
    }
}
