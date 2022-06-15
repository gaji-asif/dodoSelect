<?php

namespace App\Actions\GoogleSheet;

use Lorisleiva\Actions\Concerns\AsAction;

class TransformDate
{
    use AsAction;

    public function handle(string $date)
    {
        $explodeDate = explode('/', $date);
        $day = ($explodeDate[0] < 10) ? '0' . $explodeDate[0] : $explodeDate[0];
        $month = ($explodeDate[1] < 10) ? '0' . $explodeDate[1] : $explodeDate[1];
        $year = $explodeDate[2];

        return $year . '-' . $month . '-' . $day;
    }
}
