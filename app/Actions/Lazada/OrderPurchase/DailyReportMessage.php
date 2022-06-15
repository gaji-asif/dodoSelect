<?php

namespace App\Actions\Lazada\OrderPurchase;

use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class DailyReportMessage
{
    use AsAction;

    public function handle(string $date)
    {
        $summaryReportCurrentDate = SummaryDailyReport::make()->handle($date);

        $yesterdayDate = Carbon::createFromDate($summaryReportCurrentDate['current_date']['date'])
            ->subDay()
            ->format('Y-m-d');

        $summaryReportYesterdayDate = SummaryDailyReport::make()->handle($yesterdayDate);

        return ComposeDailyReportMessage::make()->handle($summaryReportCurrentDate, $summaryReportYesterdayDate);
    }
}
