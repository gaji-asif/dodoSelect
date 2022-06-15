<?php

namespace App\Http\Controllers\OrderManage;

use App\Actions\CreateShipmentAction;
use App\Actions\CreateShipmentActionForOrder;
use App\Actions\UpdateShipmentActionForOrder;
use App\Actions\CustomShipmentActionForOrder;
use App\Actions\UpdateCustomShipmentActionForOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderManagement\Shipment\{DeleteRequest, StoreRequest, UpdateRequest};
use App\Models\OrderManagement;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
    /**
     * Create the shipments of the orders
     *
     * @param StoreRequest $request
     * @param CreateShipmentAction $createShipmentAction
     * @return  Response
     */
    public function store(StoreRequest $request, CreateShipmentAction $createShipmentAction)
    {

        $sellerId = Auth::user()->id;

        $shipmentData = [
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'shipment_qty' => $request->shipment_qty
        ];

        $createShipmentAction->handle($shipmentData);

        return $this->apiResponse(Response::HTTP_CREATED, 'Shipment successfully created.');
    }



    public function storeForOrder(StoreRequest $request, CreateShipmentActionForOrder $createShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;

        $shipmentData = [
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'is_custom' => 0,
            'shipment_qty' => $request->shipment_qty
        ];

        $createShipmentActionForOrder->handle($shipmentData);

        return $this->apiResponse(Response::HTTP_CREATED, 'Shipment successfully created.');
    }


    public function shipmentUpdateForOrder(StoreRequest $request, UpdateShipmentActionForOrder $UpdateShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;

        $shipmentData = [
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'shipment_id' => $request->shipment_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'shipment_qty' => $request->shipment_qty
        ];

        $UpdateShipmentActionForOrder->handle($shipmentData);

        return $this->apiResponse(Response::HTTP_CREATED, 'Shipment successfully updated.');
    }

    public function storeForCustomShipment(StoreRequest $request, CustomShipmentActionForOrder $customShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;
        if(isset($request->shipment_id)){
            $shipment_id = $request->shipment_id;
        }
        else{
            $shipment_id = '';
        }

        $shipmentData = [
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'shipment_qty' => $request->shipment_qty
        ];

        $customShipmentActionForOrder->handle($shipmentData);

        return $this->apiResponse(Response::HTTP_CREATED, __('translation.New Custom Shipment successfully updated'));
    }


     public function updateForCustomShipment(StoreRequest $request, UpdateCustomShipmentActionForOrder $updatecustomShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;
        $shipmentData = [
            'shipment_id'=>$request->shipment_id,
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'shipment_qty' => $request->shipment_qty
        ];

        $updatecustomShipmentActionForOrder->handle($shipmentData);

        return $this->apiResponse(Response::HTTP_CREATED, __('translation.New Custom Shipment successfully updated'));
    }

    /**
     * Update the shipment
     * It also can changes order
     *
     * @param UpdateRequest $request
     * @return Response
     */
    public function update(UpdateRequest $request)
    {
        try {
            $sellerId = Auth::user()->id;

            $shipmentDate = null;
            if (!empty($request->shipment_date)) {
                $shipmentDate = date('Y-m-d', strtotime($request->shipment_date));
            }

            $shipment = Shipment::where('id', $request->shipment_id)->where('seller_id', $sellerId)->first();
            $shipment->shipment_date = $shipmentDate;
            $shipment->save();

            if ($request->ready_to_ship == OrderManagement::READY_TO_SHIP_YES) {
                $orderManagement = OrderManagement::where('id', $shipment->order_id)->first();
                $orderManagement->order_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP;
                $orderManagement->save();
                $shipment->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP;
                $shipment->save();
            }

            return $this->apiResponse(Response::HTTP_OK, 'Shipment successfully updated.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong. ' . $th->getMessage());
        }
    }

    /**
     * Delete shipment of the order
     * - Change status to processing, this action performed by ShipmentObserver
     *
     * @param DeleteRequest $request
     * @return Response
     */
    public function destroy(DeleteRequest $request)
    {
        try {
            $shipmentId = $request->id;
            $sellerId = Auth::user()->id;

            $shipment = Shipment::where('id', $shipmentId)->where('seller_id', $sellerId)->first();
            $shipment->delete();

            return $this->apiResponse(Response::HTTP_OK, 'Shipment successfully deleted.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong. ' . $th->getMessage());
        }
    }
}
