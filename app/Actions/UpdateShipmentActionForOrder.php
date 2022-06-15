<?php

namespace App\Actions;

use App\Models\OrderManagement;
use App\Models\OrderManagementDetail;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UpdateShipmentActionForOrder
{
    public function handle(array $data) : Shipment
    {
        try {
            DB::beginTransaction();

            $shipmentTable = (new Shipment())->getTable();
            $orderManagementTable = (new OrderManagement())->getTable();
            $shipmentProductsTable = (new ShipmentProduct())->getTable();

            $shipmentId = $data['shipment_id'];

            $shipmentDetails = Shipment::where('id', $shipmentId)->first();
            $shipmentDetails->shipment_date = date('Y-m-d', strtotime($data['shipment_date']));
            $shipmentDetails->order_id = $data['order_id'];
            $shipmentDetails->shipment_status = $data['shipment_status'];
            $shipmentDetails->seller_id = $data['seller_id'];
            $shipmentDetails->created_at = new DateTime();
            $shipmentDetails->update();

            if(isset($data['product_id'])){
                foreach ($data['product_id'] as $idx => $productId) {

                    $shipmentDetailsData = ShipmentProduct::where('shipment_id', $shipmentId)->where('product_id', $data['product_id'][$idx])->first();
                    if(!empty($data['shipment_qty'][$idx])){
                        $shipmentDetailsData->quantity = $data['shipment_qty'][$idx];
                    }
                    else{
                        $shipmentDetailsData->quantity = 0;
                    }

                    $shipmentDetailsData->updated_at = new DateTime();
                    $shipmentDetailsData->update();
                }
            }

            $ordered_products = OrderManagementDetail::where('order_management_id',$data['order_id'])->where('seller_id',Auth::user()->id)->sum('quantity');

            $total_shipped_products = ShipmentProduct::where('order_id',$data['order_id'])->sum('quantity');

            if($ordered_products == $total_shipped_products){
                $OrderManagementDetails = OrderManagement::find($data['order_id']);
                $OrderManagementDetails->order_status = OrderManagement::ORDER_STATUS_PROCESSED;
                $OrderManagementDetails->updated_at = new DateTime();
                $OrderManagementDetails->save();

            }

            elseif($ordered_products != $total_shipped_products){
                $OrderManagementDetails = OrderManagement::find($data['order_id']);
                $OrderManagementDetails->order_status = OrderManagement::ORDER_STATUS_PROCESSING;
                $OrderManagementDetails->updated_at = new DateTime();
                $OrderManagementDetails->save();
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
