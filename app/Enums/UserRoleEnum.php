<?php

namespace App\Enums;

use Closure;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self admin()
 * @method static self seller()
 * @method static self staff()
 */
final class UserRoleEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'admin' => 'admin',
            'seller' => 'member',
            'staff' => 'staff'
        ];
    }

    protected static function labels(): Closure
    {
        return fn (string $name) => ucfirst($name);
    }
}
