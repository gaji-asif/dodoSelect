<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LastChangeProductController extends Controller
{
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $cacheKey = 'dashboard.last-change-products-' . $sellerId;

        $lastChangeProducts = Cache::remember($cacheKey, 3600, function() use ($sellerId) {
            return StockLog::query()
                ->where('seller_id', $sellerId)
                ->whereHas('product')
                ->with('product')
                ->with('main_stock')
                ->with('seller')
                ->with('staff')
                ->orderBy('date', 'desc')
                ->take(10)
                ->get();
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'last_change_products' => $lastChangeProducts
        ]);
    }
}
