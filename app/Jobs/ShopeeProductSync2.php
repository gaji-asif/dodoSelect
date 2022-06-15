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
use App\Traits\ShopeeTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ShopeeProductSync2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LineBotTrait, ShopeeTrait;

    private $offset;
    private $auth_id;
    private $shopee_shop_id;
    private $per_page;

    /**
     * Create a new job instance.
     * @param $offset
     * @param $shopee_shop_id
     * @param $auth_id
     * @param $total_page_count
     */
    public function __construct($offset, $shopee_shop_id, $auth_id)
    {
        $this->offset = $offset;
        $this->shopee_shop_id = $shopee_shop_id;
        $this->auth_id = $auth_id;
        $this->per_page = 100;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        try {
            $client = $this->getShopeeClient($this->shopee_shop_id);
            if (!isset($client)) {
                return;
            }

            $response = $client->item->getItemsList([
                'pagination_offset'             => $this->offset,
                'pagination_entries_per_page'   => $this->per_page
            ]);

            $shopee_items = $response->getData()['items'];

            if(!empty($shopee_items)) {
                foreach ($shopee_items as $index => $product){
                    ShopeeProductDetailSync::dispatch($product, null, $this->auth_id)->delay(Carbon::now()->addSeconds($index*3));
                }
                $minutes = ceil(((sizeof($shopee_items)*5)+30)/60);
                // ShopeeProductSync2::dispatch($this->offset+$this->per_page, $this->shopee_shop_id, $this->auth_id)->delay(Carbon::now()->addMinutes($minutes));
                ShopeeProductSync2::dispatch($this->offset+$this->per_page, $this->shopee_shop_id, $this->auth_id)->delay(Carbon::now()->addMinutes(5));
            }
        } catch (\Exception $exception) {
            // $this->triggerPushMessage("Failed to start syncing products from \"Shopee\"");
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
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
            "Shop:{$this->shopee_shop_id}"
        ];
    }
}
