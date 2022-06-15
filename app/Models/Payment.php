<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory;

    /**
     * Define `payment method` field value
     *
     * @var mixed
     */
    CONST PAYMENT_METHOD_BANK_TRANSFER = 'Bank Transfer';

    /**
     * Define `is_confimed` field value
     *
     * @var mixed
     */
    CONST IS_CONFIRMED_NO = 0;
    CONST IS_CONFIRMED_YES = 1;


     /**
     * Define `payment_status` field value
     *
     * @var mixed
     */
    CONST PAYMENT_STATUS_NO = 0;
    CONST PAYMENT_STATUS_YES = 1;

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'payment_slip_url'
    ];

    /**
     * Accessor for `payment_slip_url` attributes
     *
     * @return string
     */
    public function getPaymentSlipUrlAttribute()
    {
        $paymentSlipAttribute = $this->attributes['payment_slip'] ?? '';

        if (!empty($paymentSlipAttribute) && Storage::disk('s3')->exists($paymentSlipAttribute)) {
            return asset(Storage::disk('s3')->url($paymentSlipAttribute));
        }

        return asset('No-Image-Found.png');
    }
}
