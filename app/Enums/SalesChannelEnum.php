<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self woocommerce()
 * @method static self shopee()
 * @method static self lazada()
 */
final class SalesChannelEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'woocommerce' => 'woo',
            'shopee' => 'shopee',
            'lazada' => 'lazada'
        ];
    }

    protected static function labels(): array
    {
        return [
            'wooocommerce' => 'WooCommerce',
            'shopee' => 'Shopee',
            'lazada' => 'Lazada'
        ];
    }
}
