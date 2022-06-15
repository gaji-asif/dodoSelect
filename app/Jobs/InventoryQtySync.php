<?php

namespace App\Jobs;

use App\Models\LazadaProduct;
use App\Models\Product;
use App\Models\ShopeeProduct;
use App\Models\WooProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InventoryQtySync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $product;
    private $child_of_quantity;
    private $process_as_batch;

    /**
     * Create a new job instance.
     * @param Product $product
     * @param null $child_of_quantity this parameter is for child products (Attribute: child_products)
     */
    public function __construct(Product $product, $child_of_quantity = null, $process_as_batch = false)
    {
        $this->product = $product;
        $this->child_of_quantity = $child_of_quantity;
        $this->process_as_batch = $process_as_batch;
    }

    /**
     * @var $product string WooCommerce product collection
     * Triggers only when stock is adjusted
     * @return void
     */
    public function handle()
    {
        if($this->child_of_quantity != null) {
            $quantity = $this->child_of_quantity;
        } else {
            $quantity = $this->product->getQuantity->quantity;
        }

        $wooProducts = WooProduct::where('dodo_product_id', $this->product->id)->get();
        if (!empty($wooProducts) && count($wooProducts) > 0) {
            InventoryQtySyncWooCommerce::dispatch($wooProducts, $quantity);
        }

        $shopeeProducts = ShopeeProduct::where('dodo_product_id', $this->product->id)->get();
        if (!empty($shopeeProducts) && count($shopeeProducts) > 0) {
            InventoryQtySyncShopee::dispatch($shopeeProducts, $quantity, $this->process_as_batch);
        }

        $lazadaProducts = LazadaProduct::where('dodo_product_id', $this->product->id)->get();
        if (!empty($lazadaProducts) && count($lazadaProducts) > 0) {
            InventoryQtySyncLazada::dispatch($lazadaProducts, $quantity);
        }
    }
}
