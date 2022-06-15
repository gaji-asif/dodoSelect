<?php

namespace App\Http\Controllers\Dashboard\Counter;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OutOfStockController extends Controller
{
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $cacheKey = 'dashboard.out-of-stock-count-' . $sellerId;

        $outOfStockCount = Cache::remember($cacheKey, 3600, function() use ($sellerId) {
            return Product::query()
                ->where('products.seller_id', $sellerId)
                ->join('product_main_stocks', 'product_main_stocks.product_id' , '=', 'products.id')
                ->where('product_main_stocks.quantity' , '<=', 0)
                ->whereRaw("IFNULL(products.alert_stock, '') <> ''")
                ->count();
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'out_of_stock' => $outOfStockCount
        ]);
    }
}
