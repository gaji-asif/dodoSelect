<?php

use Carbon\Carbon;

if (!function_exists('date_period_interval')) {
    function date_period_interval($dateFrom, $dateEnd, $intervalDays = 15) {
        $addDays = $intervalDays - 1;

        $fromDate = Carbon::createFromDate($dateFrom);
        $toDate = Carbon::createFromDate($dateEnd)->addDays(1);

        $diffDays = $fromDate->diffInDays($toDate);
        $intervalRemainder = $diffDays % $intervalDays;

        $days = [];
        while ($diffDays > 0) {
            if ($diffDays < $intervalDays) {
                $intervalDays = $intervalRemainder;
                $addDays = $intervalDays - 1;
            }

            $days[] = [
                'date' => $fromDate->format('Y-m-d'),
                'add_days' => $addDays
            ];

            $fromDate->addDays($intervalDays);
            $diffDays -= $intervalDays;
        }

        $dateRanges = [];
        foreach ($days as $day) {
            $dateRanges[] = [
                'date_from' => Carbon::createFromDate($day['date'])->format('Y-m-d'),
                'date_to' => Carbon::createFromDate($day['date'])->addDays($day['add_days'])->format('Y-m-d')
            ];
        }

        return $dateRanges;
    }
}
