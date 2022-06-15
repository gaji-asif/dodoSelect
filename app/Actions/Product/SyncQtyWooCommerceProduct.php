<?php

namespace App\Actions\Product;

use App\Jobs\WooProductQtySync;
use App\Models\Product;
use App\Models\WooProduct;
use App\Models\WooShop;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncQtyWooCommerceProduct
{
    use AsAction;

    public function handle(Product $dodoProduct, int $sellerId)
    {
        $jobDelay = 0;
        $wooProducts = WooProduct::query()
            ->where('seller_id', $sellerId)
            ->where('dodo_product_id', $dodoProduct->id)
            ->get();

        if (!empty($wooProducts)) {
            foreach ($wooProducts as $wooProduct) {
                $wooShop = WooShop::find($wooProduct->website_id);

                if (!$wooShop) {
                    continue;
                }

                WooProductQtySync::dispatch($wooShop, $wooProduct, $dodoProduct)
                    ->delay(now()->addSeconds($jobDelay));

                $jobDelay += 2;
            }
        }
    }
}
