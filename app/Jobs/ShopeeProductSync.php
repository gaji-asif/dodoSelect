<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\ShopeeProductDetailSync;
use App\Traits\LineBotTrait;
use Carbon\Carbon;
use Shopee\Client;

class ShopeeProductSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LineBotTrait;
    private $page;
    private $per_page;
    private $shop_settings;
    private $auth_id;
    private $shop_id;

    /**
     * Create a new job instance.
     * @param $page
     * @param $per_page
     * @param $shop_settings
     * @param $shop_id
     * @param $auth_id
     */
    public function __construct($page, $per_page, $shop_settings, $shop_id, $auth_id)
    {
        $this->page = $page;
        $this->per_page = $per_page;
        $this->shop_settings = $shop_settings;
        $this->shop_id = $shop_id;
        $this->auth_id = $auth_id;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        try {
            $shopeeSetting = $this->shop_settings;
            $client = new Client([
                'baseUrl' => $shopeeSetting->host,
                'secret' => $shopeeSetting->parent_key,
                'partner_id' => (int) $shopeeSetting->parent_id,
                'shopid' => (int) $this->shop_id,
            ]);

            $response = $client->item->getItemsList(
                [
                    'pagination_offset' => $this->page,
                    'pagination_entries_per_page' => $this->per_page
                ]
            );

            $shopee_items = $response->getData()['items'];

            if(!empty($shopee_items)) {
                foreach ($shopee_items as $index => $product){
                    ShopeeProductDetailSync::dispatch($product, $shopeeSetting, $this->auth_id)->delay(Carbon::now()->addSeconds($index*3));
                }
            }
        } catch (\Exception $exception) {
            $this->triggerPushMessage("Failed to start syncing products from \"Shopee\"");
        }
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
