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

class CustomShipmentActionForOrder
{
    public function handle(array $data) : Shipment
    {
        
            try {
            DB::beginTransaction();

            $shipmentTable = (new Shipment())->getTable();
            $orderManagementTable = (new OrderManagement())->getTable();
            $shipmentProductsTable = (new ShipmentProduct())->getTable();

            if(!empty($data['shipment_date'])){
                $shipment_date = date('Y-m-d', strtotime($data['shipment_date']));
            }
            else{
                $shipment_date = null;
            }   


            $shipmentDetails = new Shipment();
            $shipmentDetails->shipment_date = $shipment_date;
            $shipmentDetails->order_id = $data['order_id'];
            $shipmentDetails->is_custom = 1;
            $shipmentDetails->shipment_status = $data['shipment_status'];
            $shipmentDetails->seller_id = $data['seller_id'];
            $shipmentDetails->created_at = new DateTime();
            $shipmentDetails->shipment_for = Shipment::SHIPMENT_FOR_DODO;
            $shipmentDetails->save();
            $shipmentId =  $shipmentDetails->id;

           if(isset($data['product_id'])){
            foreach ($data['product_id'] as $idx => $productId) {
                

                $shipmentDetailsData = new ShipmentProduct();
                $shipmentDetailsData->shipment_id = $shipmentId;
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