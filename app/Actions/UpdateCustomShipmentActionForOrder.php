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

class UpdateCustomShipmentActionForOrder
{
    public function handle(array $data) : Shipment
    {
        try {
            DB::beginTransaction();

            $shipmentTable = (new Shipment())->getTable();
            $orderManagementTable = (new OrderManagement())->getTable();
            $shipmentProductsTable = (new ShipmentProduct())->getTable();

            $shipmentDetails = Shipment::find($data['shipment_id']);
            $shipmentDetails->shipment_date = date('Y-m-d', strtotime($data['shipment_date']));
            $shipmentDetails->order_id = $data['order_id'];
            $shipmentDetails->shipment_status = $data['shipment_status'];
            $shipmentDetails->updated_at = new DateTime();
            $shipmentDetails->save();

            ShipmentProduct::where('shipment_id', $data['shipment_id'])->delete();

           if(isset($data['shipment_qty'])){
            foreach ($data['shipment_qty'] as $idx => $shipment_qtys) {

                $shipmentDetailsData = new ShipmentProduct();
                $shipmentDetailsData->shipment_id = $data['shipment_id'];
                $shipmentDetailsData->order_id =  $data['order_id'];
                $shipmentDetailsData->product_id = $data['product_id'][$idx];
                if(!empty($data['shipment_qty'][$idx])){
                    $shipmentDetailsData->quantity = $data['shipment_qty'][$idx];
                }
                else{
                    $shipmentDetailsData->quantity = 0;
                }
                
                $shipmentDetailsData->updated_at = new DateTime();
                $shipmentDetailsData->save();
             }
            }

            $shipment = Shipment::where('id', $data['shipment_id'])->first();

            DB::commit();

            return $shipment;

        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            throw $th;
        }
        
     }

      
        
    
}