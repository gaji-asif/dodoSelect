<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingCostResource;
use App\Models\ShippingCost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingCostWeightController extends Controller
{
    /**
     * Get shipping cost by weight
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $sellerId = Auth::user()->id;

        $weight = floatval($request->get('weight', 0));

        $shippingCosts = ShippingCost::filterWeightBetween($weight)
                                    ->with('shipper')
                                    ->whereHas('shipper', function(Builder $shipper) use ($sellerId) {
                                        $shipper->where('seller_id', $sellerId);
                                    })
                                    ->orderBy('name', 'asc')
                                    ->get();

        $responseData = [
            'shipping_costs' => ShippingCostResource::collection($shippingCosts)
        ];

        return $this->apiResponse(200, 'Success.', $responseData);
    }
}
