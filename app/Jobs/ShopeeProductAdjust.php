<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\ShopeeProduct;
use App\Models\ShopeeSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shopee\Client;

class ShopeeProductAdjust implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $settings;
    private $item;

    /**
     * Create a new job instance.
     * @param $settings
     * @param $item
     */
    public function __construct($settings, $item)
    {
        $this->settings = $settings;
        $this->item = $item;
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
            'shopid' => (int) $this->item->website_id,
        ]);
        $response = $client->item->getItemDetail([
           'item_id' => (int) $this->item->product_id
        ]);

        if($response->getData()['item']['status'] == 'DELETED'):
            ShopeeProduct::where('product_id', $this->item->product_id)->delete();
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
            "Shop:{$this->item->website_id}"
        ];
    }
}
