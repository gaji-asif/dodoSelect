<?php

namespace App\Enums;

use Closure;
use Illuminate\Support\Str;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self escrow_verified_add()
 * @method static self escrow_verified_minus()
 * @method static self adjustment_add()
 * @method static self adjustment_minus()
 * @method static self withdrawal_created()
 * @method static self withdrawal_completed()
 * @method static self paid_ads_charge()
 */
final class ShopeeTransactionTypeEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'escrow_verified_add' => 'ESCROW_VERIFIED_ADD' ,
            'escrow_verified_minus' => 'ESCROW_VERIFIED_MINUS' ,
            'adjustment_add' => 'ADJUSTMENT_ADD' ,
            'adjustment_minus' => 'ADJUSTMENT_MINUS' ,
            'withdrawal_created' => 'WITHDRAWAL_CREATED' ,
            'withdrawal_completed' => 'WITHDRAWAL_COMPLETED' ,
            'paid_ads_charge' => 'PAID_ADS_CHARGE'
        ];
    }

    protected static function labels(): Closure
    {
        return fn (string $name) => ucwords(str_replace('_', ' ', $name));
    }
}
