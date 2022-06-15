<?php

namespace App\Enums;

use Closure;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self none()
 * @method static self syncing()
 * @method static self synced()
 */
final class SheetNameSyncStatusEnum extends Enum
{
    protected static function values(): array
    {
        return [
            'none' => 0,
            'syncing' => 1,
            'synced' => 2
        ];
    }

    protected static function labels(): Closure
    {
        return fn (string $name) => ucfirst($name);
    }
}
