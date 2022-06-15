<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\ShopeeDiscount;
use App\Traits\ShopeeTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ShopeeManageRenewableDiscounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeTrait;

    private $start_time;
    private $end_time;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->start_time = Carbon::now()->valueOf();
        $this->end_time = Carbon::now()->addDays(179)->valueOf();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shops = Shopee::get();
        foreach ($shops as $shop) {
            if (!isset($shop->shop_id)) {
                continue;
            }
            $renewable_discounts = $this->getRenewableDiscountsFromDatabase($shop->shop_id);
            if (sizeof($renewable_discounts) > 0) {
                $client = $this->getShopeeClient($shop->shop_id);
                foreach($renewable_discounts as $discount) {
                    /* Only update if only 1 day left */
                    if ($discount->end <= Carbon::now()->valueof() and 
                        Carbon::now()->diffInDays(Carbon::createFromTimestamp($discount->end)->toDateTimeString()) >= 1) {
                        continue;
                    }
                    $response = $this->updateDiscountInfoInShopee($client, $discount->discount_id);
                    if (isset($response, $response["discount_id"])) {
                        $discount->start = $this->start_time;
                        $discount->end = $this->end_time;
                        $discount->save();
                    }
                }
            }
        }
    }


    private function getRenewableDiscountsFromDatabase($shop_id)
    {
        return ShopeeDiscount::whereRenewable("yes")
            ->whereStatus("ongoing")
            ->whereWebsiteId($shop_id)
            ->get();
    }


    private function updateDiscountInfoInShopee($client, $discount_id)
    {
        try {
            return $client->discount->updateDiscount([
                "discount_id"   => $discount_id,
                "start_time"    => $this->start_time,
                "end_time"      => $this->end_time,
                "end_discount"  => false
            ]);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }
}
