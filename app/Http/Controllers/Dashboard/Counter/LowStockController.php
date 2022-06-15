<?php

namespace App\Http\Controllers\Dashboard\Counter;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductMainStock;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LowStockController extends Controller
{
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $cacheKey = 'dashboard.low-stock-count-' . $sellerId;

        $lowStockCount = Cache::remember($cacheKey, 3600, function() use ($sellerId) {
            $products = Product::query()
                ->selectRaw('products.id, products.alert_stock,
                    product_main_stocks.quantity AS current_stock')
                ->where('products.seller_id', $sellerId)
                ->join('product_main_stocks', 'product_main_stocks.product_id' , '=', 'products.id')
                ->where('product_main_stocks.quantity' , '>', 0)
                ->get();

            $filteredProducts = $products->filter(function ($item, $key) {
                return $item->current_stock <= $item->alert_stock;
            });

            return $filteredProducts->count();
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'low_stock' => $lowStockCount
        ]);
    }
}
