<?php

namespace App\Http\Controllers\Dashboard\Counter;

use App\Http\Controllers\Controller;
use App\Models\OrderManagement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OrdersToProcessController extends Controller
{
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $cacheKey = 'dashboard.order-to-process-count-' . $sellerId;

        $ordersToProcess = Cache::remember($cacheKey, 3600, function () use ($sellerId) {
            return OrderManagement::query()
                ->where('seller_id', $sellerId)
                ->where('order_status', OrderManagement::ORDER_STATUS_PROCESSING)
                ->count();
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'orders_to_process' => $ordersToProcess
        ]);
    }
}
