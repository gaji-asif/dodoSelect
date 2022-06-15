<?php

namespace App\Jobs;

use App\Traits\ShopeeOrderPurchaseTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteShopeeProductImageFromShopee implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait;
    private $shopee_shop_id, $product_id, $images, $positions;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $product_id, $images, $positions=[])
    {
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->product_id = (int) $product_id;
        $this->images = (array) $images;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (sizeof($this->images) == 0) {
            return;
        }
        $client = $this->getShopeeClient($this->shopee_shop_id);
        if (isset($client)) {
            $response = $client->item->deleteItemImg([
                "item_id" => $this->product_id,
                "images"  => $this->images  
            ]);
        }
    }
}
