<?php

namespace App\Http\Controllers\OrderManage;

use App\Http\Controllers\Controller;
use App\Models\OrderManagement;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use App\Models\Channel;
use App\Models\CustomerShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use PDF;

class ShipmentLabelController extends Controller
{
    /**
     * Print the pdf of shipment label
     *
     * @param  integer  $orderId
     * @return  PDF
     */
    public function printShipmentPdf($orderId, $shipmentId)
    {
        $sellerId = Auth::user()->id;

        $orderManagement = OrderManagement::where('id', $orderId)
                            ->where('seller_id', $sellerId)
                            ->with(['order_management_details' => function($detail) {
                                $detail->with('product');
                            }])
                            ->with('customer')
                            ->with('shop')
                            ->with('shipment')
                            ->first();

        if($shipmentId){
            $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$shipmentId)
                        ->leftJoin('products', 'shipment_products.product_id', '=', 'products.id')
                        ->select('products.*', 'shipment_products.quantity as shipped_qty')
                        ->get();         
        }

        if(isset($orderManagement->shop_id)){
            $shopName = $orderManagement->shop->name;
        }
        else{
            $shopName = '';
        }

        if(isset($orderManagement->channel_id)){
            $channelDetails = Channel::where('id',$orderManagement->channel_id)->first();
            if(isset($channelDetails->name)){
            $channelName = $channelDetails->name;
        }
            else{
                $channelName = '';
            }
        }

        
        $shippingMethod = '';
        foreach ($orderManagement->customer_shipping_methods as $customerShipping){
            if ($customerShipping->is_selected == CustomerShippingMethod::IS_SELECTED_YES){
                $shippingMethod = $customerShipping->shipping_cost->name;
            }
        }
        

        abort_if(!$orderManagement, Response::HTTP_NOT_FOUND, 'Order data not found');

        $pdfFileName = 'shipment-label_' . $orderManagement->id . '.pdf';

        $data = [
            'orderManagement' => $orderManagement,
            'title' => $pdfFileName,
            'shopName'=>$shopName,
            'channelName'=>$channelName,
            'shippingMethod'=>$shippingMethod,
            'getShipmentsProductsDetails'=>$getShipmentsProductsDetails
        ];

        $shipmentLabelPdf = PDF::loadView('pdf.shipment-label', $data);

        if ($shipmentLabelPdf) {
            $shipment = Shipment::where('id', $shipmentId)->first();

            if (empty($shipment->print_date_time)) {
                $shipment->print_date_time = date('Y-m-d H:i:s');
                $shipment->print_status = Shipment::PRINT_STATUS_PRINTED;
                $shipment->print_by = $sellerId;
                $shipment->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED;
                $shipment->update();
            }
        }

        return $shipmentLabelPdf->download($pdfFileName);
    }
}
