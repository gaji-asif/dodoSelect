<?php

namespace App\Http\Controllers\BuyerPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyerPage\CancelOrderRequest;
use App\Models\OrderManagement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CancelOrderController extends Controller
{
    /**
     * Cancel the order
     *
     * @param  \App\Http\Requests\BuyerPage\CancelOrderRequest  $request
     * @param  string  $orderId
     * @return \Illuminate\Http\Response
     */
    public function store(CancelOrderRequest $request, $orderId)
    {
        try {
            $orderManagement = OrderManagement::where('order_id', $orderId)->first();
            $orderManagement->order_status = OrderManagement::ORDER_STATUS_CANCEL;
            $orderManagement->save();

            return $this->apiResponse(Response::HTTP_OK, 'Order has been updated.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong');
        }
    }
}
