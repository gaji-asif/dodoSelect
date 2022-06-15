<?php

namespace App\Actions;

use App\Models\OrderManagement;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use DateTime;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CreateShipmentAction
{
    public function handle(array $data) : Shipment
    {
        try {
            DB::beginTransaction();
            $shipmentDate = null;
            if (!empty($data['shipment_date']) && $data['shipment_status'] == Shipment::SHIPMENT_STATUS_READY_TO_SHIP) {
                $shipmentDate = date('Y-m-d', strtotime($data['shipment_date']));
            }

            $orderManagementdata = OrderManagement::find($data['order_id']);
            $orderManagementdata->order_status = OrderManagement::ORDER_STATUS_PROCESSED;
            $orderManagementdata->updated_at = new DateTime();
            $orderManagementdata->save();


            $shipmentData = new Shipment();
            $shipmentData->shipment_date = $shipmentDate;
            $shipmentData->order_id = $data['order_id'];
            $shipmentData->shipment_status = $data['shipment_status'];
            $shipmentData->seller_id = $data['seller_id'];
            $shipmentData->created_at = new DateTime();
            $shipmentData->save();
            $shipmentId = $shipmentData->id;


            if(isset($data['product_id'])){
                foreach ($data['product_id'] as $idx => $productId) {
                    $shipmentDetailsData = new ShipmentProduct();
                    $shipmentDetailsData->shipment_id = $shipmentId;
                    $shipmentDetailsData->order_id = $data['order_id'];
                    $shipmentDetailsData->product_id = $data['product_id'][$idx];
                    $shipmentDetailsData->quantity = $data['shipment_qty'][$idx] ?? 0;
                    $shipmentDetailsData->created_at = new DateTime();
                    $shipmentDetailsData->save();

                 }
            }

            $shipment = Shipment::where('id', $shipmentId)->first();

            DB::commit();

            return $shipment;

        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            throw $th;
        }
    }
}
