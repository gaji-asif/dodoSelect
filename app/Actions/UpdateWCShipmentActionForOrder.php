<?php

namespace App\Actions;

use App\Jobs\AdjustDisplayReservedQty;
use App\Models\WooOrderPurchase;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use App\Traits\Inventory\AdjustDisplayReservedQtyTrait;
use App\Traits\Inventory\WooCommerceInventoryProductsStockUpdateTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UpdateWCShipmentActionForOrder
{
    use WooCommerceInventoryProductsStockUpdateTrait, AdjustDisplayReservedQtyTrait;

    public function handle(array $data) : Shipment
    {
        try {
            DB::beginTransaction();

            $shipmentTable = (new Shipment())->getTable();
            $WooOrderPurchaseTable = (new WooOrderPurchase())->getTable();
            $shipmentProductsTable = (new ShipmentProduct())->getTable();

            $shipmentId = $data['shipment_id'];

            $shipmentDetails = Shipment::where('id', $shipmentId)->first();
            $shipmentDetails->shipment_date = date('Y-m-d', strtotime($data['shipment_date']));
            $shipmentDetails->order_id = $data['order_id'];
            $shipmentDetails->shipment_status = $data['shipment_status'];
            $shipmentDetails->shipment_for = $data['shipment_for'];
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
     
            $ordered_products = 0;
            $ordered_details = WooOrderPurchase::where('website_id',$shipmentDetails->shop_id)->where('order_id',$data['order_id'])->where('seller_id',Auth::user()->id)->first();
            $orderProductDetails = json_decode($ordered_details->line_items);
            if(!empty($orderProductDetails)){
                foreach ($orderProductDetails as $product) {
                    $ordered_products += $product->quantity;
                }
            }

            $total_shipped_products = ShipmentProduct::where('shipment_products.order_id',$data['order_id'])
                ->where('shipments.shipment_for',Shipment::SHIPMENT_FOR_WOO)
                ->where('shipments.is_custom',0)
                ->where('shipments.shop_id',$shipmentDetails->shop_id)
                ->leftJoin('shipments', 'shipments.id', '=', 'shipment_products.shipment_id')
                ->sum('shipment_products.quantity');

            
            if($ordered_products == $total_shipped_products){
                $ordered_details->status = WooOrderPurchase::ORDER_STATUS_PROCESSED;
                $ordered_details->updated_at = new DateTime();
                $ordered_details->save();

                /**
                 * Update "display_reserved_qty" for the dodo products in this order.
                 * NOTE:
                 * This will be triggered for any other status/status_custom other than "processing".
                 */
                if ($this->checkIfDisplayReservedQtyShouldBeUpdated($data['order_id'], $shipmentDetails->shop_id, $this->getTagForWooCommercePlatform())) {
                    AdjustDisplayReservedQty::dispatch($data['order_id'], $shipmentDetails->shop_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
                }
            }
            if($ordered_products != $total_shipped_products){
                $ordered_details->status = WooOrderPurchase::ORDER_STATUS_PROCESSING;
                $ordered_details->updated_at = new DateTime();
                $ordered_details->save();

                /* Update inventory quantity */
                if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($data['order_id'], $shipmentDetails->shop_id, $this->getTagForWooCommercePlatform())) {
                    $this->initInventoryQtyUpdateForWooCommerce($ordered_details);
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
