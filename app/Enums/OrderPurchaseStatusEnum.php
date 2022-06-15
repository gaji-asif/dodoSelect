<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self open()
 * @method static self close()
 * @method static self arrive()
 * @method static self draft()
 */
final class OrderPurchaseStatusEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'open' => __('translation.Open'),
            'close' => __('translation.Close'),
            'arrive' => __('translation.Arrive'),
            'draft' => __('translation.Draft')
        ];
    }
}
