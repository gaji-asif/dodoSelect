<?php

namespace App\Jobs;

use App\Models\ShopeeSetting;
use App\Traits\Inventory\MonitorAdjustProductQtyTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Shopee\Client;

class InventoryQtySyncShopeeOld implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MonitorAdjustProductQtyTrait;

    /** @var Collection */
    private $shopeeProducts;

    /** @var int */
    private $quantity;

    /** @var array */
    private $shopeeShopIds;

    /**
     * Create a new job instance.
     *
     * @param  Collection  $shopeeProducts
     * @param  int  $quantity
     * @return void
     */
    public function __construct($shopeeProducts, $quantity)
    {
        $this->shopeeProducts = $shopeeProducts;
        $this->quantity = $quantity;

        $this->shopeeShopIds = $this->shopeeProducts->pluck('website_id')->unique()->values()->all();
    }

    /**
     * @var $product string WooCommerce product collection
     * Triggers only when stock is adjusted
     * @return void
     */
    public function handle()
    {
        $shopeeProducts = $this->shopeeProducts;
        foreach ($shopeeProducts as $product) {
            $shopeeSetting = ShopeeSetting::first();
            $shop_id = (int) $product->website_id;

            $client = new Client([
                'baseUrl' => $shopeeSetting->host,
                'secret' => $shopeeSetting->parent_key,
                'partner_id' => (int) $shopeeSetting->parent_id,
                'shopid' => (int) $shop_id
            ]);

            if($product->type == 'simple'):
                $response = $client->item->updateStock(
                    [
                        'item_id' => (int) $product->product_id,
                        'stock' => (int) $product->quantity,
                    ]
                );
            else:
                $response = $client->item->updateVariationStock(
                    [
                        'item_id' => (int) $product->parent_id,
                        'variation_id' => (int) $product->product_id,
                        'stock' => (int) $product->quantity,
                    ]
                );
            endif;

            if ($this->shouldUpdateProductQty($response->getData(), $this->getTagForShopeePlatform())) {
                $product->quantity = $this->quantity;
                $product->update();
            } else {
                $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForShopeePlatform());
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return collect($this->shopeeShopIds)->map(function ($shopId) {
                return "Shop:{$shopId}";
        })->toArray();
    }
}
