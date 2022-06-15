<?php

namespace App\Actions\SheetDataTpk;

use App\Models\SheetDataTpk;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class SummaryDailyReport
{
    use AsAction;

    public function handle(string $dateParameter = null)
    {
        try {
            $date = Carbon::parse($dateParameter)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = Carbon::now()->format('Y-m-d');
        }

        $monthToDateFirstDate = Carbon::parse($date)
            ->startOfMonth()
            ->format('Y-m-d');

        $lastMonthFirstDate = Carbon::createFromDate($date)
            ->subMonthNoOverflow()
            ->startOfMonth()
            ->format('Y-m-d');

        $lastMonthLastDate = Carbon::createFromDate($date)
            ->subMonthNoOverflow()
            ->lastOfMonth()
            ->format('Y-m-t');

        $currentDayOrdersByShop = SheetDataTpk::query()
            ->selectRaw("shop, SUM(amount) AS total_amount, COUNT(id) AS total_orders")
            ->whereBetween('date', [ $date, $date ])
            ->with('shopData')
            ->groupBy('shop')
            ->get();

        $totalAmount = $currentDayOrdersByShop->sum('total_amount');
        $totalOrders = $currentDayOrdersByShop->sum('total_orders');

        $monthToDateOrders = SheetDataTpk::query()
            ->selectRaw("SUM(amount) AS total_amount, COUNT(id) AS total_orders")
            ->whereBetween('date', [ $monthToDateFirstDate, $date ])
            ->first();

        $lastMonthOrders = SheetDataTpk::query()
            ->selectRaw("SUM(amount) AS total_amount, COUNT(id) AS total_orders")
            ->whereBetween('date', [ $lastMonthFirstDate, $lastMonthLastDate ])
            ->first();

        return [
            'current_date' => [
                'date' => $date,
                'total_amount' => $totalAmount,
                'total_orders' => $totalOrders,
                'orders_by_shop' => $currentDayOrdersByShop
            ],
            'month_to_date' => [
                'first_date' => $monthToDateFirstDate,
                'current_date' => $date,
                'total_amount' => $monthToDateOrders->total_amount ?? 0,
                'total_orders' => $monthToDateOrders->total_orders ?? 0
            ],
            'last_month' => [
                'first_date' => $lastMonthFirstDate,
                'last_date' => $lastMonthLastDate,
                'total_amount' => $lastMonthOrders->total_amount ?? 0,
                'total_orders' => $lastMonthOrders->total_orders ?? 0
            ],
        ];
    }
}
