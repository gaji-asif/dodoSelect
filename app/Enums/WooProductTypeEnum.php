<?php

namespace App\Enums;

use Closure;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self simple()
 * @method static self variable()
 */
final class WooProductTypeEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'simple' => 'simple',
            'variable' => 'variable'
        ];
    }

    protected static function labels(): Closure
    {
        return fn (string $name) => ucfirst($name);
    }
}
