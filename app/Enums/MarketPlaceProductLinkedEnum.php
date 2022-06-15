<?php

namespace App\Enums;

use Closure;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self no()
 * @method static self yes()
 */
final class MarketPlaceProductLinkedEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'no' => 0,
            'yes' => 1
        ];
    }

    protected static function labels(): Closure
    {
        return fn (string $name) => ucfirst($name);
    }
}
