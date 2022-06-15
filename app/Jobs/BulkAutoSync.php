<?php

namespace App\Jobs;

use App\Models\Lazada;
use App\Models\LazadaProduct;
use App\Models\LazadaSetting;
use App\Models\Product;
use App\Models\ShopeeProduct;
use App\Models\ShopeeSetting;
use App\Models\WooProduct;
use App\Models\WooShop;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Lazada\LazopClient;
use Lazada\LazopRequest;
use Shopee\Client;

class BulkAutoSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;

    /**
     * Create a new job instance.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job
     * @throws Exception
     */
    public function handle()
    {
        $quantity = $this->product->getQuantity->quantity;

        $wooProducts = WooProduct::where('dodo_product_id', $this->product->id)->get();
        if (!empty($wooProducts) && count($wooProducts) > 0) {
            InventoryQtySyncWooCommerce::dispatch($wooProducts, $quantity);
        }

        $shopeeProducts = ShopeeProduct::where('dodo_product_id', $this->product->id)->get();
        if (!empty($shopeeProducts) && count($shopeeProducts) > 0) {
            InventoryQtySyncShopee::dispatch($shopeeProducts, $quantity);
        }

        $lazadaProducts = LazadaProduct::where('dodo_product_id', $this->product->id)->get();
        if (!empty($lazadaProducts) && count($lazadaProducts) > 0) {
            InventoryQtySyncLazada::dispatch($lazadaProducts, $quantity);
        }
    }
}
