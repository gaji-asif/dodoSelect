<?php

namespace App\Console;

use App\Console\Commands\ImportThailandAdmin;
use App\Jobs\CheckAuthorizationExpirationForShops;
use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\LazadaOrderAutoSyncEveryInterval;
use App\Jobs\LazadaSyncShipmentProviders;
use App\Jobs\OrderWiseProductsAvailableQtyUpdateTrackerRemove;
use App\Jobs\ShopeeManageRenewableDiscounts;
use App\Jobs\ShopeeMonitorBoostedProduct;
use App\Jobs\ShopeeOrderAutoSyncEveryInterval;
use App\Jobs\ShopeeOrderIncomeAutoSync;
use App\Jobs\ShopeeProductCategoriesSync;
use App\Jobs\ShopeeTransactionAutoSync;
use App\Jobs\WooWebhookSync;
use Carbon\Carbon;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    /*protected $commands = [
        Commands\ExcelReport::class,
        ImportThailandAdmin::class
    ];*/
    protected $commands = [
        ImportThailandAdmin::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('telescope:prune --hours=48')->daily();

        $schedule->command('dodo:sheet-data-tpk-sync')->everyTenMinutes();

        $schedule->command('dodo:daily-report-tpk-sale')->dailyAt('11:00');
        $schedule->command('dodo:daily-report-shopee-sale')->dailyAt('11:00');

        $schedule->command('dodo:daily-report-tpk-sale')->dailyAt('19:30');
        $schedule->command('dodo:daily-report-shopee-sale')->dailyAt('19:30');

        $schedule->command('dodo:daily-report-lazada-sale')->dailyAt('11:40');
        $schedule->command('dodo:daily-report-lazada-sale')->dailyAt('11:50');

        $schedule->command('dodo:daily-report-lazada-sale')->dailyAt('12.05');
        $schedule->command('dodo:daily-report-lazada-sale')->dailyAt('19:30');

        //$schedule->command('excel:report')->dailyAt('15:00');

        /** Check expiration auth for shopee shops */
        $schedule->job(new CheckAuthorizationExpirationForShops())->dailyAt('12:00');

        /* Sync orders autometically from "Shopee". */
        // $schedule->job(new ShopeeOrderAutoSyncEveryInterval(-1, 0))->everyTwoHours();
        $schedule->job(new ShopeeOrderAutoSyncEveryInterval(-1, 0, true))->cron('0 */1 * * *');

        /* Sync orders autometically from "Lazada". */
        // $schedule->job(new LazadaOrderAutoSyncEveryInterval(-1, 0, "auto"))->everyThreeHours();
        $schedule->job(new LazadaOrderAutoSyncEveryInterval(-1, 0, "auto"))->cron('0 */1 * * *');

        /* Sync in Shopee product categories. */
        $schedule->job(new ShopeeProductCategoriesSync())->dailyAt('15:00');

        /* Sync in Lazada shipment provider. */
        $schedule->job(new LazadaSyncShipmentProviders())->dailyAt('15:00');

        /** Sync Shopee Wallet Transaction */
        $schedule->job(new ShopeeTransactionAutoSync())->everyThreeHours();

        /** Sync Shopee Order Income */
        $schedule->job(new ShopeeOrderIncomeAutoSync())->everyTwoHours();

        /** Monitor Shopee boosted products */
        $schedule->job(new ShopeeMonitorBoostedProduct())->everyFifteenMinutes();

        /* Update old orders from Shopee and Lazada which were not processed. */
        $schedule->call(function (){
            \App\Jobs\ShopeeSyncOlderOrderPurchaseDetails::dispatch();
            \App\Jobs\LazadaSyncOlderOrderPurchaseDetails::dispatch()->delay(Carbon::now()->addMinutes(5));
        })->daily('02:00');
        $schedule->command('dodo:woocommerce-webhook-status-update')
             ->everySixHours();
        //$schedule->job(new WooWebhookSync())->everySixHours();

        /*$schedule->call(function (){
            $shopeeSetting = ShopeeSetting::first();
            $products = ShopeeProduct::all();
            foreach ($products as $product):
                ShopeeProductAdjust::dispatch($shopeeSetting, $product)->delay(Carbon::now()->addSeconds(2));
            endforeach;
        })->everyMinute();*/

        /* Delete old data from database used to track for which orders the products inventory has been updated. */
        $schedule->job(new OrderWiseProductsAvailableQtyUpdateTrackerRemove())->dailyAt('06:00');

        /* Manage renewable discounts(coupons) for Shopee. */
        $schedule->job(new ShopeeManageRenewableDiscounts())->dailyAt('00:00');
    }


    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return 'Asia/Bangkok';
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
