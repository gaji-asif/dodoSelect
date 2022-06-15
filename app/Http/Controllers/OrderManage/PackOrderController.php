<?php

namespace App\Http\Controllers\OrderManage;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderManagement\Shipment\PackOrderRequest;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PackOrderController extends Controller
{
    /**
     * Update pack order of shipment
     * - It also change the order_status to shipped, performed by ShipmentObserver
     *
     * @param PackOrderRequest $request
     * @return Response
     */
    public function update(PackOrderRequest $request)
    {
        try {
            $shipmentId = $request->id;

            $shipment = Shipment::where('id', $shipmentId)->first();
            $shipment->pack_status = Shipment::PACK_STATUS_PACKED;
            $shipment->packed_date_time = date('Y-m-d H:i:s');
            $shipment->packed_by = Auth::user()->id;
            $shipment->save();

            return $this->apiResponse(Response::HTTP_OK, 'Data successfully updated.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }
    }
}
