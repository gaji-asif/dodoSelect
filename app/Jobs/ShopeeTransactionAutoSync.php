<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\ShopeeSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShopeeTransactionAutoSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var ShopeeSetting */
    private $shopeeSetting;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->shopeeSetting = ShopeeSetting::first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shopTake = 50;
        $shopPage = 0;
        $shopCount = Shopee::count();

        $delaySeconds = 0;
        $shopeeIncomeParameters = [
            'create_time_from' => strtotime(now()->subDay(1)->format('Y-m-d 00:00:00')),
            'create_time_to' => strtotime(now()->format('Y-m-d 23:59:59'))
        ];

        while ($shopCount >= 0) {
            $shopSkip = $shopTake * $shopPage;

            $shops = Shopee::query()
                ->with('seller')
                ->take($shopTake)
                ->skip($shopSkip)
                ->get();

            foreach ($shops as $shop) {
                ShopeeTransactionSync::dispatch(
                    $this->shopeeSetting,
                    $shop->shop_id,
                    $shop->seller,
                    $shopeeIncomeParameters
                )->delay(now()->addSeconds($delaySeconds));

                $delaySeconds += 3;
            }

            $shopPage++;
            $shopCount -= $shopTake;
        }
    }
}
