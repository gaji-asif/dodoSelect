<?php

namespace App\Http\Controllers\BuyerPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyerPage\ShippingAddress\CheckAddress;
use App\Http\Requests\BuyerPage\ShippingAddress\UpdateRequest;
use App\Models\OrderManagement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShippingAddressController extends Controller
{
    /**
     * Update the shipping_x field
     * on order_managements table
     *
     * @param  \App\Http\Requests\BuyerPage\ShippingAddress\UpdateRequest  $request
     * @param  string  $orderId
     * @return  \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $orderId)
    {
        try {
            $orderManagement = OrderManagement::where('order_id', $orderId)->first();
            $orderManagement->shipping_address = $request->shipping_address;
            $orderManagement->shipping_name = $request->shipping_name;
            $orderManagement->shipping_phone = $request->shipping_phone;
            $orderManagement->shipping_district = $request->shipping_district;
            $orderManagement->shipping_sub_district = $request->shipping_sub_district;
            $orderManagement->shipping_province = $request->shipping_province;
            $orderManagement->shipping_postcode = $request->shipping_postcode;
            $orderManagement->save();

            return $this->apiResponse(Response::HTTP_OK, 'Data successfully updated.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong.');
        }
    }

    public function checkShippingAddress(CheckAddress $request)
    {
        try {
            return $this->apiResponse(Response::HTTP_OK, 'Data successfully checked.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong.');
        }
    }
}
