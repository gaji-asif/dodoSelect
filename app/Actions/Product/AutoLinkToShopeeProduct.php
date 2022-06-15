<?php

namespace App\Actions\Product;

use App\Enums\MarketPlaceProductLinkedEnum;
use App\Enums\MarketPlaceProductStatusEnum;
use App\Models\Product;
use App\Models\ShopeeProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class AutoLinkToShopeeProduct
{
    use AsAction;

    public function handle(Product $dodoProduct, int $sellerId)
    {
        $shopeeProducts = ShopeeProduct::query()
            ->where('seller_id', $sellerId)
            ->where('product_code', $dodoProduct->product_code)
            ->where('is_linked', MarketPlaceProductLinkedEnum::no()->value)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->get();

        if (!empty($shopeeProducts)) {
            foreach ($shopeeProducts as $product){
                $product->dodo_product_id = $dodoProduct->id;
                $product->is_linked = MarketPlaceProductLinkedEnum::yes()->value;
                $product->save();
            }
        }
    }
}
