<?php

namespace App\Actions;

use App\Models\OrderManagement;
use App\Models\OrderManagementDetail;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CreateShipmentActionForOrder
{
    public function handle(array $data) : Shipment
    {
        try {
            DB::beginTransaction();

            $shipmentTable = (new Shipment())->getTable();
            $shipmentProductsTable = (new ShipmentProduct())->getTable();

            $shipment_date = null;
            if (!empty($data['shipment_date'])){
                $shipment_date = date('Y-m-d', strtotime($data['shipment_date']));
            }

            $shipmentId = DB::table($shipmentTable)
                    ->insertGetId([
                        'shipment_date' => $shipment_date,
                        'order_id' => $data['order_id'],
                        'shipment_status' => $data['shipment_status'],
                        'seller_id' => $data['seller_id'],
                        'is_custom' => $data['is_custom'],
                        'shipment_for' => Shipment::SHIPMENT_FOR_DODO,
                        'created_at' => new DateTime()
                    ]);

            if(isset($data['product_id'])){
            foreach ($data['product_id'] as $idx => $productId) {
                $shipmentDetailsData = [
                    'shipment_id' => $shipmentId,
                    'order_id' => $data['order_id'],
                    'product_id' => $data['product_id'][$idx],
                    'quantity' => $data['shipment_qty'][$idx] ?? 0,
                    'created_at' => new DateTime()
                ];

                DB::table($shipmentProductsTable)->insert($shipmentDetailsData);
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
            if($ordered_products != $total_shipped_products){

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
