<?php

namespace App\Http\Controllers\OrderManage;

use App\Http\Controllers\Controller;
use App\Models\OrderManagementDetail;
use App\Models\ShipmentProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackOrderProductController extends Controller
{
    /**
     * Handle pack order product data
     * for datatable
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $orderId = $request->get('orderId', 0);
        $shipmentId = $request->get('shipmentId', 0);

        $orderDetails = OrderManagementDetail::where('order_management_id', $orderId)
                    ->with('product')
                    ->get();

        $shipments = ShipmentProduct::where('shipment_id', $shipmentId)->get();

        $productData = [];

        foreach ($orderDetails as $detail) {
            $row = [];
            $row[] = '
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <img src="'. $detail->product->image_url .'" class="w-16 md:w-11/12 h-auto" />
                    </div>
                    <div>
                        <span class="whitespace-nowrap text-blue-500">
                            ID : <strong>'. $detail->product->id .'</strong>
                        </span>
                    </div>
                </div>
            ';

            $productPrice = $detail->price - $detail->discount_price;

            $shippedProducts = $detail->quantity;
            foreach ($shipments as $shipment){
                if ($shipment->product_id == $detail->product_id){
                    $shippedProducts = $shipment->quantity;
                }
            }

            $row[] = '
                <div>
                    <div class="mb-1">
                        <strong>'. $detail->product->product_name .'</strong>
                    </div>
                    <div class="mb-1">
                        <strong class="text-blue-500">'. $detail->product->product_code .'</strong>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700">
                                Price :
                            </label>
                            <span>'. currency_symbol('THB') . number_format(floatval($productPrice), 2) .'</span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700">
                                Shipment Quantity :
                            </label>
                            <span class="text-gray-900">
                                '. number_format($shippedProducts) .'
                            </span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700">
                                Total Price :
                            </label>
                            <strong class="">'. currency_symbol('THB') . number_format(floatval($productPrice * $shippedProducts), 2) .'</strong>
                        </div>
                    </div>
                </div>
            ';

            $productData[] = $row;
        }

        return response()->json([
            'data' => $productData,
        ]);
    }
}
