<?php

namespace App\Actions\Product;

use App\Jobs\InventoryQtySyncLazada;
use App\Models\Lazada;
use App\Models\LazadaProduct;
use App\Models\LazadaSetting;
use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncQtyLazadaProduct
{
    use AsAction;

    public function handle(Product $dodoProduct, int $sellerId)
    {
        $jobDelay = 0;
        $lazadaSetting = LazadaSetting::first();
        $lazadaProducts = LazadaProduct::query()
            ->where('seller_id', $sellerId)
            ->where('dodo_product_id', $dodoProduct->id)
            ->get();

        if (!empty($lazadaProducts)) {
            foreach ($lazadaProducts as $lazadaProduct) {
                $lazadaShop = Lazada::find($lazadaProduct->website_id);

                if (empty($lazadaShop)) {
                    continue;
                }

                InventoryQtySyncLazada::dispatch($lazadaSetting, $lazadaShop, $lazadaProduct, $dodoProduct)
                    ->delay(now()->addSeconds($jobDelay));

                $jobDelay += 2;
            }
        }
    }
}
