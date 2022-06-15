<?php

namespace App\Actions\Shopee\OrderPurchase;

use App\Actions\Traits\MathCalculation;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class ComposeDailyReportMessage
{
    use AsAction;
    use MathCalculation;

    public function handle(array $summaryReportCurrentDate, array $summaryReportYesterdayDate)
    {
        $currencySymbol = currency_symbol('THB');

        $lastMonthTotalAmount = $summaryReportCurrentDate['last_month']['total_amount'];
        $strlastMonthOrders = number_format($lastMonthTotalAmount, 2);

        $currentDateDate = $summaryReportCurrentDate['current_date']['date'];
        $strLastMonthName = Carbon::createFromDate($currentDateDate)->subMonth()->format('M Y');

        $monthToDateTotalAmount = $summaryReportCurrentDate['month_to_date']['total_amount'];
        $strmonthToDateTotalAmount = number_format($monthToDateTotalAmount, 2);

        $currentDateTotalAmount = $summaryReportCurrentDate['current_date']['total_amount'];
        $strCurrentDateTotalAmount = number_format($currentDateTotalAmount, 2);

        $currentDateTotalOrders = $summaryReportCurrentDate['current_date']['total_orders'];
        $strCurrentDateTotalOrders = number_format($currentDateTotalOrders);
        $textOrders = Str::plural('Order', $currentDateTotalOrders);

        $strTodayDate = date('d/m/Y', strtotime($currentDateDate));

        $strCurrentHour = '';
        if ($currentDateDate == date('Y-m-d')) {
            $strCurrentHour = date('H:i');
        }

        $message = "Shopee \n\n";
        $message .= "{$strTodayDate} - Update {$strCurrentHour}\n";
        $message .= "Total Orders :\n";
        $message .= "  {$currencySymbol}{$strCurrentDateTotalAmount} ({$strCurrentDateTotalOrders} {$textOrders})\n";
        $message .= '---------------------------------------------------------------';

        $currentDateOrdersByShop = $summaryReportCurrentDate['current_date']['orders_by_shop'];
        $yesterdayOrdersByShop = $summaryReportYesterdayDate['current_date']['orders_by_shop'];

        foreach ($currentDateOrdersByShop as $order) {
            $yesterdayData = $yesterdayOrdersByShop->where('website_id', $order->website_id)->first();
            $yesterdayTotalAmount = $yesterdayData->total_amount ?? 0;

            $shopName = $order->shopee->shop_name ?? 'N/A';
            $shopAmount = number_format($order->total_amount, 2);
            $shopOrders = number_format($order->total_orders);
            $textOrders = Str::plural('Order', $shopOrders);

            $yesterdayShopAmount = number_format($yesterdayTotalAmount, 2);
            $percentageAmount = $this->calculatePercentage($order->total_amount, $yesterdayTotalAmount);

            $message .= "\n- {$shopName} : \n";
            $message .= "   Today : {$currencySymbol}{$shopAmount} ({$shopOrders} {$textOrders}) \n";
            $message .= "   Yesterday : {$currencySymbol}{$yesterdayShopAmount} [{$percentageAmount}%] \n";
        }

        $message .= "\n---------------------------------------------------------------\n";
        $message .= "Month to date :\n";
        $message .= "  {$currencySymbol}{$strmonthToDateTotalAmount}\n";
        $message .= "Last month ({$strLastMonthName}) :\n";
        $message .= "  {$currencySymbol}{$strlastMonthOrders}";

        return $message;
    }
}
