<?php

namespace App\Enums;

use Closure;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self publish()
 */
final class MarketPlaceProductStatusEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'publish' => 'publish'
        ];
    }

    protected static function labels(): Closure
    {
        return fn (string $name) => ucfirst($name);
    }
}
