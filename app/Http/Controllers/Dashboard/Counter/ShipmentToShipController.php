<?php

namespace App\Http\Controllers\Dashboard\Counter;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ShipmentToShipController extends Controller
{
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $cacheKey = 'dashboard.shipment-to-ship-count-' . $sellerId;

        $shipmentToShip = Cache::remember($cacheKey, 3600, function () use ($sellerId) {
            return Shipment::query()
                ->where('seller_id', $sellerId)
                ->where(function ($shipment) {
                    $shipment->where('shipment_status', Shipment::SHIPMENT_STATUS_READY_TO_SHIP)
                        ->orWhere('shipment_status', Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED);
                })
                ->count();
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'shipment_to_ship' => $shipmentToShip
        ]);
    }
}
