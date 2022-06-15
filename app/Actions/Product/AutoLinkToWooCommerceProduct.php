<?php

namespace App\Actions\Product;

use App\Enums\MarketPlaceProductLinkedEnum;
use App\Enums\MarketPlaceProductStatusEnum;
use App\Enums\WooProductTypeEnum;
use App\Models\Product;
use App\Models\WooProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class AutoLinkToWooCommerceProduct
{
    use AsAction;

    public function handle(Product $dodoProduct, int $sellerId)
    {
        $wooProducts = WooProduct::query()
            ->where('seller_id', $sellerId)
            ->where('product_code', $dodoProduct->product_code)
            ->where('is_linked', MarketPlaceProductLinkedEnum::no()->value)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->where('type', '<>', WooProductTypeEnum::variable()->value)
            ->get();

        if (!empty($wooProducts)) {
            foreach ($wooProducts as $product) {
                $product->dodo_product_id = $dodoProduct->id;
                $product->is_linked = MarketPlaceProductLinkedEnum::yes()->value;
                $product->save();
            }
        }
    }
}
