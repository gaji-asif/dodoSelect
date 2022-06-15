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

class BulkAutoLink implements ShouldQueue
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
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $wooProducts = WooProduct::where('product_code', $this->product->product_code)->where('is_linked', 0)->get();
        if (count($wooProducts) > 0) {
            foreach ($wooProducts as $wooProduct) {
                $wooProduct->dodo_product_id = $this->product->id;
                $wooProduct->is_linked = 1;
                $wooProduct->update();
            }
        }

        $shopeeProducts = ShopeeProduct::where('product_code', $this->product->product_code)->where('is_linked', 0)->get();
        if (count($shopeeProducts) > 0) {
            foreach ($shopeeProducts as $shopeeProduct) {
                $shopeeProduct->dodo_product_id = $this->product->id;
                $shopeeProduct->is_linked = 1;
                $shopeeProduct->update();
            }
        }

        $lazadaProducts = LazadaProduct::where('product_code', $this->product->product_code)->where('is_linked', 0)->get();
        if (count($lazadaProducts) > 0) {
            foreach ($lazadaProducts as $lazadaProduct) {
                $lazadaProduct->dodo_product_id = $this->product->id;
                $lazadaProduct->is_linked = 1;
                $lazadaProduct->update();
            }
        }
    }
}
