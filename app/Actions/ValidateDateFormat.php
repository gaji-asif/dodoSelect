<?php

namespace App\Actions;

use DateTime;
use Lorisleiva\Actions\Concerns\AsAction;

class ValidateDateFormat
{
    use AsAction;

    public function handle(?string $dateValue, string $expectedFormat = 'Y-m-d')
    {
        if (empty($dateValue)) {
            return false;
        }

        $createdDate = DateTime::createFromFormat($expectedFormat, $dateValue);

        return $createdDate && ($createdDate->format($expectedFormat) === $dateValue);
    }
}
