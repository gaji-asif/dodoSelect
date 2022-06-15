<?php

namespace App\Actions\Product;

use App\Enums\MarketPlaceProductLinkedEnum;
use App\Enums\MarketPlaceProductStatusEnum;
use App\Models\LazadaProduct;
use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class AutoLinkToLazadaProduct
{
    use AsAction;

    public function handle(Product $dodoProduct, int $sellerId)
    {
        $lazadaProducts = LazadaProduct::query()
            ->where('seller_id', $sellerId)
            ->where('product_code', $dodoProduct->product_code)
            ->where('is_linked', MarketPlaceProductLinkedEnum::no()->value)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->get();

        if (!empty($lazadaProducts)) {
            foreach ($lazadaProducts as $product) {
                $product->dodo_product_id = $dodoProduct->id;
                $product->is_linked = MarketPlaceProductLinkedEnum::yes()->value;
                $product->save();
            }
        }
    }
}
