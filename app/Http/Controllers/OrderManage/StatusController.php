<?php

namespace App\Http\Controllers\OrderManage;

use App\Http\Controllers\Controller;
use App\Models\OrderManagement;
use App\Models\WooOrderPurchase;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    use ApiResponse;

    /**
     * Get status counters of order_managements data
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sellerId = Auth::user()->id;
        $roleName = Auth::user()->role;

        $allAvailableOrderStatus = OrderManagement::getAllOrderStatus();

        $orderStatusCounters = OrderManagement::selectRaw('order_status, COUNT(id) AS total')
                    ->where('seller_id', $sellerId)
                    ->customerAsset($roleName)
                    ->groupBy('order_status')
                    ->orderBy('order_status', 'asc')
                    ->get();

        $orderStatusTransformed = collect($allAvailableOrderStatus)->map(function($orderLabel, $orderId) use ($orderStatusCounters) {
            $counter = $orderStatusCounters->where('order_status', $orderId)->first();

            return [
                'id' => $orderId,
                'label' => $orderLabel,
                'total' => $counter->total ?? 0
            ];
        });

        $orderStatusTransformed->prepend([
            'id' => -1,
            'label' => 'All',
            'total' => $orderStatusTransformed->sum('total')
        ]);

        return $this->apiResponse(200, 'Success', [
            'orderStatuses' => $orderStatusTransformed
        ]);
    }

    /**
     * Get status counters of woo purchase orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderStatusList(Request $request)
    {
        $parentStatusId = $request->get('parentStatusId', 0);
        $customerType = $request->get('customerType', 0);
        $roleName = Auth::user()->role;

        $statusSchema = OrderManagement::getStatusSchemaForDatatable($roleName,$customerType );
        $statusCounts = '';
        foreach ($statusSchema as $schema){
            if ($schema['id'] == $parentStatusId){
                $statusCounts = $schema['sub_status'];
            }
        }

        $data = [
            'orderStatusCounts' => $statusCounts
        ];

        return response()->json($data);
    }

    /**
     * Get status counters of woo purchase orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWooStatusList(Request $request)
    {
        $parentStatusId = $request->get('parentStatusId', 0);
        $shopId = $request->get('shopId', 0);

        $statusMainSchema = WooOrderPurchase::getMainStatusSchemaForDatatable($shopId);
        
        $statusSecondarySchema = WooOrderPurchase::getSecondaryStatusSchemaForDatatable($shopId);

        $statusCounts = '';
        $tabCounts = [];
        foreach ($statusMainSchema as $schema){
            if ($schema['id'] == $parentStatusId){
                $statusCounts = $schema['sub_status'];
            }
            $tabCounts[$schema['id']] = $schema['count'];
        }

        foreach ($statusSecondarySchema as $schema){
            if ($schema['id'] == $parentStatusId){
                $statusCounts = $schema['sub_status'];
            }
            $tabCounts[$schema['id']] = $schema['count'];
        }

        $data = [
            'orderStatusCounts' => $statusCounts,
            'tabCounts' => $tabCounts
        ];

        return response()->json($data);
    }

    
    /**
     * Get status counters of woo purchase orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWooOrderStatusList(Request $request)
    {
        $parentStatusId = $request->get('parentStatusId', 0);
        $shopId = $request->get('shopId', 0);
        //dd($parentStatusId);
        $statusMainSchema = WooOrderPurchase::getMainStatusSchemaForDatatable();
        $statusSecondarySchema = WooOrderPurchase::getSecondaryStatusSchemaForDatatable();

        $tabCounts = '';
        $statusCounts = [];
        foreach ($statusMainSchema as $key=>$status){
            if ($key == 0){
                $statusCounts = $status['sub_status'];
            }
        }

        $data = [
            'orderStatusCounts' => $statusCounts
        ];

        return response()->json($data);
    }




        /**
     * Get status counters of woo purchase orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWooShipmentStatusList(Request $request)
    {
        $parentStatusId = $request->get('parentStatusId', 0);
        $shopId = $request->get('shopId', 0);

        $statusMainSchema = WooOrderPurchase::getShipmentStatusSchemaForDatatable($shopId);
     
        $statusCounts = '';
        $tabCounts = [];
        foreach ($statusMainSchema as $schema){
            
            $statusCounts = $schema['sub_status'];           
            $tabCounts[$schema['id']] = $schema['count'];
        }

     

        $data = [
            'shipmentStatusCount' => $statusCounts,
            'tabCount' => $tabCounts
        ];

        return response()->json($data);
    }
}
