<?php

namespace App\Enums;

use Closure;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self attach()
 * @method static self detach()
 */
final class ProductLinkActionEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'attach' => 'attach',
            'detach' => 'detach'
        ];
    }

    protected static function labels(): Closure
    {
        return fn (string $name) => ucfirst($name);
    }
}
