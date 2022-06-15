<?php

namespace App\Jobs;

use App\Models\ShopeeDiscount;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Shopee\Client;

class ShopeeDiscountSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $settings;
    private $shop_id;

    /**
     * Create a new job instance.
     * @param $settings
     * @param $shop_id
     */
    public function __construct($settings, $shop_id)
    {
        $this->settings = $settings;
        $this->shop_id = $shop_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client([
            'baseUrl' => $this->settings->host,
            'secret' => $this->settings->parent_key,
            'partner_id' => (int) $this->settings->parent_id,
            'shopid' => (int) $this->shop_id,
        ]);

        $response = $client->discount->getDiscountsList([
            'discount_status' => 'ALL',
            'pagination_offset' => 0,
            'pagination_entries_per_page' => 100
        ]);

        $discounts = $response->getData()['discount'];
        foreach ($discounts as $item) {
            $discount = ShopeeDiscount::firstOrNew([
                'discount_id' => (int) $item['discount_id'],
                'website_id'  => $this->shop_id
            ]);
            $discount->discount_id = (int) $item['discount_id'];
            $discount->website_id = $this->shop_id;
            $discount->name = $item['discount_name'];
            $discount->status = $item['status'];
            $discount->start = $item['start_time'];
            $discount->end = $item['end_time'];
            $discount->save();
        }

        if($response->getData()['more']):
            ShopeeDiscountSync::dispatch($this->settings, $this->shop_id)->delay(Carbon::now()->addSeconds(1));
        endif;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->shop_id}"
        ];
    }
}
