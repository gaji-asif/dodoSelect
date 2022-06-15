<?php

namespace App\Actions\Traits;

trait MathCalculation
{
    public function calculatePercentage($todayTotalAmount, $yesterdayTotalAmount)
    {
        $diffAmount = $todayTotalAmount - $yesterdayTotalAmount;

        if ($yesterdayTotalAmount <= 0) {
            return 100;
        }

        return number_format($diffAmount / $yesterdayTotalAmount * 100, 2);
    }
}