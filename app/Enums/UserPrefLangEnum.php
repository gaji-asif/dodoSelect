<?php

namespace App\Enums;

use Closure;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self english()
 * @method static self thai()
 */
final class UserPrefLangEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'english' => 'en',
            'thai' => 'th'
        ];
    }

    protected static function labels(): Closure
    {
        return fn (string $name) => ucfirst($name);
    }
}
