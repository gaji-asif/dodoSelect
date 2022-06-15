<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LatestStockProductController extends Controller
{
    public function __invoke(string $type)
    {
        $sellerId = Auth::user()->id;

        $availableType = [
            'add' => StockLog::CHECK_IN_OUT_ADD,
            'remove' => StockLog::CHECK_IN_OUT_REMOVE
        ];

        $checkInOut = $availableType[$type] ?? null;

        abort_if(is_null($checkInOut), Response::HTTP_NOT_FOUND);

        $cacheKey = 'dashboard.latest-stock-products-' . $type . '-' . $sellerId;

        $latestStockProducts = Cache::remember($cacheKey, 3600, function() use ($sellerId, $checkInOut) {
            return StockLog::query()
                ->where('seller_id', $sellerId)
                ->where('check_in_out', $checkInOut)
                ->whereHas('product')
                ->with('product')
                ->with('main_stock')
                ->with('seller')
                ->with('staff')
                ->orderBy('quantity', 'desc')
                ->orderBy('date', 'desc')
                ->take(5)
                ->get();
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'latest_stock_products' => $latestStockProducts
        ]);
    }
}
