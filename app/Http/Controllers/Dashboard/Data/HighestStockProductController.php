<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HighestStockProductController extends Controller
{
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $cacheKey = 'dashboard.highest-stock-product-' . $sellerId;

        $highestStockProducts = Cache::remember($cacheKey, 3600, function() use ($sellerId) {
            return StockLog::query()
                ->where('seller_id', $sellerId)
                ->whereHas('product')
                ->with(['product' => function ($product) {
                    $product->with('seller');
                }])
                ->orderBy('quantity', 'desc')
                ->take(5)
                ->get();
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'highest_stock_products' => $highestStockProducts
        ]);
    }
}
