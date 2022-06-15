<?php

namespace App\Jobs;

use App\Models\ShopeeOrderPurchase;
use App\Models\ShopeeSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShopeeOrderIncomeAutoSync implements ShouldQueue
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
        $totalOrderPurchase = ShopeeOrderPurchase::query()
            ->doesntHave('shopee_income')
            ->count();

        $take = 50;
        $page = 0;
        $delayInSecond = 0;

        while ($totalOrderPurchase >= 0) {
            $skip = $take * $page;

            $orderPurchases = ShopeeOrderPurchase::query()
                ->doesntHave('shopee_income')
                ->has('shopee')
                ->has('seller')
                ->with('shopee')
                ->with('seller')
                ->take($take)
                ->skip($skip)
                ->get();

            foreach ($orderPurchases as $order) {
                ShopeeOrderIncomeSync::dispatch(
                        $this->shopeeSetting,
                        $order->shopee->shop_id,
                        $order->seller,
                        $order->order_id
                    )
                    ->delay(now()->addSeconds($delayInSecond));

                $delayInSecond += 2;
            }

            $totalOrderPurchase -= $take;
            $page++;
        }
    }
}
