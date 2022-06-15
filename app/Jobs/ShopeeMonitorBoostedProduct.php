<?php

namespace App\Jobs;

use App\Models\Shopee;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShopeeMonitorBoostedProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shopee_shops = Shopee::get();
        foreach ($shopee_shops as $index => $shop) {
            if (isset($shop->shop_id)) {
                ShopeeGetBoostedProduct::dispatch($shop->shop_id)->delay(Carbon::now()->addSeconds($index*10));
            }
        }
    }
}
