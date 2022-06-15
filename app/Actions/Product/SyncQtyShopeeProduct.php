<?php

namespace App\Actions\Product;

use App\Jobs\ShopeeProductQtySync;
use App\Models\Product;
use App\Models\ShopeeProduct;
use App\Models\ShopeeSetting;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncQtyShopeeProduct
{
    use AsAction;

    public function handle(Product $dodoProduct, int $sellerId)
    {
        $jobDelay = 0;
        $shopeeSetting = ShopeeSetting::first();
        $shopeeProducts = ShopeeProduct::query()
            ->where('seller_id', $sellerId)
            ->where('dodo_product_id', $dodoProduct->id)
            ->get();

        if (!empty($shopeeProducts)) {
            foreach ($shopeeProducts as $shopeeProduct) {
                $shop_id = (int) $shopeeProduct->website_id;

                ShopeeProductQtySync::dispatch($shopeeSetting, $shop_id, $shopeeProduct, $dodoProduct)
                    ->delay(now()->addSeconds($jobDelay));

                $jobDelay += 2;
            }
        }
    }
}
