<?php

namespace App\Http\Controllers\Dashboard\Counter;

use App\Http\Controllers\Controller;
use App\Models\OrderPurchase;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OrdersTodayController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today()->format('Y-m-d');
        $sellerId = Auth::user()->id;

        $cacheKey = 'dashboard.orders-today-count-' . $sellerId;

        $ordersToday = Cache::remember($cacheKey, 3600, function () use ($today, $sellerId) {
            return OrderPurchase::getTodaysPODataBySellerID($today, $sellerId);
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'orders_today' => $ordersToday
        ]);
    }
}
