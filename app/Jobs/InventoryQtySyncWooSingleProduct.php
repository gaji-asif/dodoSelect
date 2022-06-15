<?php

namespace App\Jobs;

use App\Models\WooShop;
use App\Traits\Inventory\MonitorAdjustProductQtyTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class InventoryQtySyncWooSingleProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MonitorAdjustProductQtyTrait;

    private $wooProduct;
    private $quantity;

    /**
     * Create a new job instance.
     *
     * @param $wooProduct
     * @param $quantity
     */
    public function __construct($wooProduct, $quantity)
    {
        $this->wooProduct = $wooProduct;
        $this->quantity = $quantity;
    }

    /**
     * @var $product string WooCommerce product collection
     * Triggers only when stock is adjusted
     * @return void
     */
    public function handle()
    {
        $wooProduct = $this->wooProduct;
        $quantity = $this->quantity;

        $shop = WooShop::find($wooProduct->website_id);
        if($shop !== null) {
            if($wooProduct->type == 'simple') {
                $url = $shop->site_url . '/wp-json/wc/v3/products/' . $wooProduct->product_id
                    . '?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;
            } else {
                $url = $shop->site_url . '/wp-json/wc/v3/products/' . $wooProduct->parent_id .'/variations/'. $wooProduct->product_id
                    . '?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;
            }

            $data = array(
                'manage_stock' => true,
                'stock_quantity' => $quantity,
            );

            $response = Http::put($url, $data);
            if ($this->shouldUpdateProductQty($response, $this->getTagForWooCommercePlatform())) {
                $wooProduct->quantity = $quantity;
                $wooProduct->update();
            } else {
                $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($wooProduct, $quantity, $this->getTagForWooCommercePlatform());
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return string
     */
    public function tags()
    {
        return "ShopID:{$this->wooProduct->website_id}";
    }
}
