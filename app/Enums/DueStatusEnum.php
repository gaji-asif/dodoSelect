<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self arrive_soon()
 * @method static self overdue()
 */
final class DueStatusEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'arrive_soon' => __('translation.Arrive Soon'),
            'overdue' => __('translation.Overdue')
        ];
    }
}
