<?php

namespace App\Http\Controllers;

use App\Jobs\LazadaOrderPurchaseSyncOrderItemDetails;
use App\Jobs\LazadaOrderSetStatusToPackedByMarketplaceInBulk;
use App\Jobs\LazadaOrderSetStatusToReadyToShipInBulk;
use App\Jobs\LazadaSyncSpecificOrderPurchaseDetail;
use App\Models\Lazada;
use App\Models\LazadaOrderPurchase;
use App\Models\LazadaOrderPurchaseItem;
use App\Traits\LazadaOrderPurchaseTrait;
use App\Traits\LazadaOrderSyncTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaOrderStatusController extends Controller
{
    use LazadaOrderPurchaseTrait, LazadaOrderSyncTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get status counters for lazada orders.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLazadaStatusList(Request $request)
    {
        $parentStatusId = $request->get('parentStatusId', 0);
        $shopId = $request->get('shopId', 0);

        $statusMainSchema = LazadaOrderPurchase::getMainStatusSchemaForDatatable($shopId);
        $statusSecondarySchema = LazadaOrderPurchase::getSecondaryStatusSchemaForDatatable($shopId);

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
            'tabCounts' => $tabCounts,
            'shopsToProcessCounts' => $this->getLazadaShopsWithProcessingOrdersCount(implode(",", array_column($statusMainSchema[0]['sub_status'], 'id'))),
        ];

        return response()->json($data);
    }


    /**
     * Get the total number orders falling unded "To Process". Basically orders having "processing", "retry_ship" and "in_cancel" 
     * as a value for "status_custom" falls under "To Process".
     * $processingStatuses may contain one or more statuses seperated by comma.
     * 
     * @param string $processingStatuses
     * @return array
     */
    private function getLazadaShopsWithProcessingOrdersCount($processingStatuses) 
    {
        try {
            $shops = Lazada::where('seller_id', $this->getLazadaSellerId())
                ->select('id','shop_name','shop_id','code')
                ->orderBy('shop_name', 'asc')
                ->get();
            foreach($shops as $shop) {
                $shop["processing_orders_count"] = !empty($processingStatuses)?LazadaOrderPurchase::getMultipleStatusSchemaCount($processingStatuses, $shop["id"]):0;
                $date = $this->getBulkSyncStartTimeCacheValue($shop->id, $this->getLazadaSellerId());
                $shop["orders_last_synced_at"] = !empty($date)?Carbon::parse($date)->format("d/m/Y H:i A"):Carbon::now()->addMinutes(30)->format("d/m/Y H:i A");
            }
            return $shops;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStatusToPackedByMarketplace(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (isset($request->shipping_provider, $request->delivery_type, $request->order_id) and 
                    !empty($request->shipping_provider) and !empty($request->delivery_type) and !empty($request->order_id)
                    and in_array($request->delivery_type, $this->allowedShipingMethodsForLazada())) {
                    $order_purchase = LazadaOrderPurchase::whereOrderId($request->order_id)->latest()->first();
                    if (!isset($order_purchase)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Invalid Order")
                        ]);
                    }
                    $lazada_shop = Lazada::find($order_purchase->website_id);
                    if (!isset($lazada_shop)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Invalid Lazada Shop")
                        ]);
                    }
                    $access_token = $this->getAccessTokenForLazada($order_purchase->website_id);
                    if (!empty($access_token)) {
                        $order_items = "";
                        if (isset($order_purchase->order_item_ids) and !empty($order_purchase->order_item_ids)) {
                            $order_items = $order_purchase->order_item_ids;
                        }
                        $client = $this->getLazadaClient();
                        $obj = $this->getRequestObjectToSetStatusToPackedByMarketplace([
                            "shipping_provider" => $request->shipping_provider,
                            "delivery_type"     => $request->delivery_type,
                            "order_item_ids"    => $order_items    
                        ]);
                        if (isset($client, $obj)) {
                            $response = $client->execute($obj, $access_token);
                            if (isset($response) and $this->isJson($response)) {
                                $data = json_decode($response);
                                if (isset($data->data, $data->data->order_items)) {
                                    foreach ($data->data->order_items as $order_item) {
                                        $tracking_number = $order_item->tracking_number;
                                        if (isset($tracking_number) and (!isset($order_purchase->tracking_number) || empty($order_purchase->tracking_number))) {    
                                            /* Update the "tracking_number" of the order. */
                                            $order_purchase->tracking_number = $tracking_number;
                                            // $order_purchase->order_id = $order_item->purchase_order_id;
                                            $order_purchase->order_number = isset($order_item->purchase_order_number)?$order_item->purchase_order_number:$order_purchase->order_number;
                                            $order_purchase->package_id = isset($order_item->package_id)?$order_item->package_id:$order_purchase->package_id;
                                            $order_purchase->shipment_provider = isset($order_item->shipment_provider)?$order_item->shipment_provider:$order_purchase->shipment_provider;
                                            $order_purchase->save();
                                        }
                                        $this->updateItemInDatabaseAfterStatusToPackedByMarketplace($order_item);
                                    }
                                }
                            }
                        }
                        /* Fetch order detail from Lazada */
                        LazadaSyncSpecificOrderPurchaseDetail::dispatch($order_purchase->website_id, $order_purchase->order_id, $this->getLazadaSellerId(), $access_token);
                        /* Fetch order item details from Lazada */
                        LazadaOrderPurchaseSyncOrderItemDetails::dispatch($order_purchase->website_id, $order_purchase->order_id, $access_token);

                        return response()->json([
                            "success"   => true,
                            "message"   => __("translation.Successfully updated status to be \"Packed\".")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Falied to update status to be packed."
        ]);
    }


    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStatusToPackedByMarketplaceInBulk(Request $request)
    {
        try {   
            if ($request->ajax()) {
                if (isset($request->shipping_provider, $request->delivery_type, $request->json_data) and 
                    !empty($request->shipping_provider) and !empty($request->delivery_type) 
                    and in_array($request->delivery_type, $this->allowedShipingMethodsForLazada())) {

                    $arr = json_decode($request->json_data);
                    if (sizeof($arr) == 0) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.You have to select orders first.")
                        ]);
                    }
                    if (sizeof($arr) > 50) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.You can select atmost 50 orders at a time.")
                        ]);
                    }
                    
                    $ordersn_list = [];
                    $ordersn_list_data = [];
                    $seller_id = $this->getLazadaSellerId();     

                    $lazada_shops = Lazada::get();
                    foreach ($lazada_shops as $lazada_shop) {
                        $ordersn_list[$lazada_shop->id] = [];
                    }

                    foreach ($arr as $web_order_data) {
                        $order_data = explode("*", $web_order_data);
                        /* $order_data[0] is 'website_id'('id' in 'lazada' table), $order_data[1] is 'id'('lazada_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                        array_push($ordersn_list[(int) $order_data[0]], $order_data[2]);
                    }
   
                    foreach ($lazada_shops as $index => $lazada_shop) {
                        if (isset($ordersn_list[$lazada_shop->id]) and sizeof($ordersn_list[$lazada_shop->id]) > 0) {
                            $access_token = $this->getAccessTokenForLazada($lazada_shop->id);
                            if (empty($access_token)) {
                                continue;
                            }

                            $shop_specific_ordersn_list = [];
                            foreach ($ordersn_list[$lazada_shop->id] as $order_id) {
                                /* Check if the order ids are already processing. */
                                $cache_val = $this->getLazadaOrderProcessingRelatedInCacheForTrackingInit($order_id, $seller_id);
                                if (!isset($cache_val)) {
                                    /* Update cache. This will be used to show whether the order is processing currently or not. */
                                    $this->putLazadaOrderProcessingRelatedInCacheForTrackingInit($order_id, "processing", $seller_id);
                                    array_push($shop_specific_ordersn_list, $order_id);
                                }
                            }

                            if (sizeof($shop_specific_ordersn_list) > 0) {
                                /* Update status to "packed" for Lazada orders in bulk. */
                                LazadaOrderSetStatusToPackedByMarketplaceInBulk::dispatch($request->shipping_provider, $request->delivery_type, $access_token, $shop_specific_ordersn_list, $seller_id)->delay(Carbon::now()->addSeconds($index*10));
                                
                                $ordersn_list_data = array_merge($ordersn_list_data, $shop_specific_ordersn_list);
                            }
                        }
                    }

                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "ordersn_list"  => $ordersn_list_data
                        ],
                        "message"   => __("translation.Successfully started process for updating status to \"Packed\".")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Falied to update status to be \"Packed\" in bulk.")
        ]);
    }


    /**
     * Store items info in database after status changed to "packed".
     * 
     * @param object $order_item
     */
    private function updateItemInDatabaseAfterStatusToPackedByMarketplace($order_item)
    {
        try {
            if (isset($order_item->order_item_id)) {
                $order_purchase_item = LazadaOrderPurchaseItem::whereOrderItemId($order_item->order_item_id)->latest()->first();
                if (isset($order_purchase_item)) {
                    if (isset($order_item->purchase_order_id)) {
                        $order_purchase_item->purchase_order_id = $order_item->purchase_order_id;
                    }
                    if (isset($order_item->purchase_order_number)) {
                        $order_purchase_item->purchase_order_number = $order_item->purchase_order_number;
                    }
                    if (isset($order_item->package_id)) {
                        $order_purchase_item->package_id = $order_item->package_id;
                    }
                    if (isset($order_item->shipment_provider)) {
                        $order_purchase_item->shipment_provider = $order_item->shipment_provider;
                    }
                    $order_purchase_item->save();
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    } 


    /**
     * Get the request object for setting status to packed for orders in Lazada.
     * 
     * @param array $params
     */
    private function getRequestObjectToSetStatusToPackedByMarketplace($params=[]) 
    {
        try {
            $request = new LazopRequest('/order/pack', 'POST');
            if (isset($params["shipping_provider"]) and !empty($params["shipping_provider"])) {
                $request->addApiParam('shipping_provider', $params["shipping_provider"]);
            }
            if (isset($params["delivery_type"]) and !empty($params["delivery_type"]) and 
                in_array($params["delivery_type"], $this->allowedShipingMethodsForLazada())) {
                $request->addApiParam('delivery_type', $params["delivery_type"]);
            }
            if (isset($params["order_item_ids"])) {
                if ($this->isJson($params["order_item_ids"])) {
                    $request->addApiParam('order_item_ids', $params["order_item_ids"]);
                } else {
                    if (sizeof($params["order_item_ids"]) > 0) {
                        $request->addApiParam('order_item_ids', json_encode($params["order_item_ids"]));
                    }
                }
            }
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStatusToReadyToShip(Request $request)
    {
        try {   
            if ($request->ajax()) {
                if (isset($request->order_id, $request->tracking_number) and 
                    !empty($request->order_id) and !empty($request->tracking_number)) {
                    $order_purchase = LazadaOrderPurchase::whereOrderId($request->order_id)->latest()->first();
                    if (!isset($order_purchase, $order_purchase->shipment_provider, $order_purchase->delivery_type)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Invalid Order")
                        ]);
                    }
                    $lazada_shop = Lazada::find($order_purchase->website_id);
                    if (!isset($lazada_shop)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Invalid Lazada Shop")
                        ]);
                    }
                    $access_token = $this->getAccessTokenForLazada($order_purchase->website_id);
                    if (!empty($access_token)) {
                        $order_items = "";
                        if (isset($order_purchase->order_item_ids) and !empty($order_purchase->order_item_ids)) {
                            $order_items = $order_purchase->order_item_ids;
                        }
                        $client = $this->getLazadaClient();
                        $obj = $this->getRequestObjectToSetStatusToReadyToShip([
                            "shipping_provider" => $order_purchase->shipping_provider,
                            "delivery_type"     => $order_purchase->delivery_type,
                            "order_item_ids"    => $order_items,
                            "tracking_number"   => $request->tracking_number    
                        ]);
                        if (isset($client, $obj)) {
                            $response = $client->execute($obj, $access_token);
                            if (isset($response) and $this->isJson($response)) {
                                $data = json_decode($response);
                                if (isset($data->data, $data->data->order_items)) {
                                    foreach ($data->data->order_items as $order_item) {
                                        $this->updateItemInDatabaseAfterSetStatusToReadyToShip($order_item);
                                    }
                                }
                            }
                        }
                        /* Fetch order detail from Lazada */
                        LazadaSyncSpecificOrderPurchaseDetail::dispatch($order_purchase->website_id, $order_purchase->order_id, $this->getLazadaSellerId(), $access_token);
                        /* Fetch order item details from Lazada */
                        LazadaOrderPurchaseSyncOrderItemDetails::dispatch($order_purchase->website_id, $order_purchase->order_id, $access_token);
                        
                        return response()->json([
                            "success"   => true,
                            "message"   => __("translation.Successfully updated status to \"Ready To Ship\".")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Falied to update status \"ready to ship\".")
        ]);
    }


    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStatusToReadyToShipInBulk(Request $request)
    {
        try {   
            if ($request->ajax()) {
                if (isset($request->json_data)) {
                    $arr = json_decode($request->json_data);
                    if (sizeof($arr) == 0) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.You have to select orders first.")
                        ]);
                    }
                    if (sizeof($arr) > 50) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.You can select atmost 50 orders at a time.")
                        ]);
                    }
                    
                    $ordersn_list = [];
                    $ordersn_list_data = [];
                    $seller_id = $this->getLazadaSellerId();     

                    $lazada_shops = Lazada::get();
                    foreach ($lazada_shops as $lazada_shop) {
                        $ordersn_list[$lazada_shop->id] = [];
                    }

                    foreach ($arr as $web_order_data) {
                        $order_data = explode("*", $web_order_data);
                        /* $order_data[0] is 'website_id'('id' in 'lazada' table), $order_data[1] is 'id'('lazada_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                        array_push($ordersn_list[(int) $order_data[0]], $order_data[2]);
                    }

                    foreach ($lazada_shops as $index => $lazada_shop) {
                        if (isset($ordersn_list[$lazada_shop->id]) and sizeof($ordersn_list[$lazada_shop->id]) > 0) {
                            $access_token = $this->getAccessTokenForLazada($lazada_shop->id);
                            if (empty($access_token)) {
                                continue;
                            }

                            $shop_specific_ordersn_list = [];
                            foreach ($ordersn_list[$lazada_shop->id] as $order_id) {
                                /* Check if the order ids are already processing. */
                                $cache_val = $this->getLazadaOrderProcessingRelatedInCacheForTrackingInit($order_id, $seller_id);
                                if (!isset($cache_val)) {
                                    /* Update cache. This will be used to show whether the order is processing currently or not. */
                                    $this->putLazadaOrderProcessingRelatedInCacheForTrackingInit($order_id, "processing", $seller_id);
                                    array_push($shop_specific_ordersn_list, $order_id);
                                }
                            }

                            if (sizeof($shop_specific_ordersn_list) > 0) {
                                /* Update status to "ready to ship" for Lazada orders in bulk. */
                                LazadaOrderSetStatusToReadyToShipInBulk::dispatch($access_token, $shop_specific_ordersn_list, $seller_id)->delay(Carbon::now()->addSeconds($index*10));
                                
                                $ordersn_list_data = array_merge($ordersn_list_data, $shop_specific_ordersn_list);
                            }
                        }
                    }

                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "ordersn_list"  => $ordersn_list_data
                        ],
                        "message"   => __("translation.Successfully started process for updating status to \"Ready To Ship\".")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Falied to update status to be packed."
        ]);
    }


    /**
     * Store items info in database after status changed to "ready to ship".
     * 
     * @param object $order_item
     */
    private function updateItemInDatabaseAfterSetStatusToReadyToShip($order_item)
    {
        try {
            if (isset($order_item->order_item_id)) {
                $order_purchase_item = LazadaOrderPurchaseItem::whereOrderItemId($order_item->order_item_id)->latest()->first();
                if (isset($order_purchase_item)) {
                    if (isset($order_item->purchase_order_id)) {
                        $order_purchase_item->purchase_order_id = $order_item->purchase_order_id;
                    }
                    if (isset($order_item->purchase_order_number)) {
                        $order_purchase_item->purchase_order_number = $order_item->purchase_order_number;
                    }
                    if (isset($order_item->package_id)) {
                        $order_purchase_item->package_id = $order_item->package_id;
                    }
                    if (isset($order_item->shipment_provider)) {
                        $order_purchase_item->shipment_provider = $order_item->shipment_provider;
                    }
                    $order_purchase_item->save();
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    } 


    /**
     * Get the request object for setting status to "ready to ship" for orders in Lazada.
     * 
     * @param array $params
     */
    private function getRequestObjectToSetStatusToReadyToShip($params=[]) 
    {
        try {
            $request = new LazopRequest('/order/rts', 'POST');
            if (isset($params["shipping_provider"]) and !empty($params["shipping_provider"])) {
                $request->addApiParam('shipping_provider', $params["shipping_provider"]);
            }
            if (isset($params["delivery_type"]) and !empty($params["delivery_type"]) and 
                in_array($params["delivery_type"], $this->allowedShipingMethodsForLazada())) {
                $request->addApiParam('delivery_type', $params["delivery_type"]);
            }
            if (isset($params["order_item_ids"])) {
                if ($this->isJson($params["order_item_ids"])) {
                    $request->addApiParam('order_item_ids', $params["order_item_ids"]);
                } else {
                    $request->addApiParam('order_item_ids', json_encode($params["order_item_ids"]));
                }
            }
            if (isset($params["tracking_number"]) and !empty($params["tracking_number"])) {
                $request->addApiParam('tracking_number', $params["tracking_number"]);
            }
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Set status to canceled for lazada purchase orders.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStatusToCanceled(Request $request)
    {
        try {   
            if ($request->ajax()) {
                if (isset($request->reason_detail, $request->reason_id, $request->order_item_id) 
                    and !empty($request->reason_id) and !empty($request->order_item_id)) {
                    $order_purchase_item = LazadaOrderPurchaseItem::whereOrderItemId($request->order_item_id)->latest()->first();
                    if (!isset($order_purchase_item)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Invalid Order")
                        ]);
                    }
                    $lazada_shop = Lazada::find($order_purchase_item->website_id);
                    if (!isset($lazada_shop)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Invalid Lazada Shop")
                        ]);
                    }
                    $access_token = $this->getAccessTokenForLazada($order_purchase_item->website_id);
                    if (!empty($access_token)) {
                        $client = $this->getLazadaClient();
                        $obj = $this->getRequestObjectToSetStatusToCanceled([
                            "reason_detail"     => $request->reason_detail,
                            "delivery_type"     => $request->delivery_type,
                            "order_items_id"    => $request->order_item_id
                        ]);
                        if (isset($client, $obj)) {
                            $response = $client->execute($obj, $access_token);
                            if (isset($response) and $this->isJson($response)) {
                                $data = json_decode($response);
                                if ($data->code == 0) {
                                    return response()->json([
                                        "success"   => true,
                                        "message"   => __("translation.Succesfully cancel the item in the order.")
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Falied to cancel the item in the order."
        ]);
    }


    /**
     * Get the request object for setting status to "cancelled" for orders in Lazada.
     * 
     * @param array $params
     */
    private function getRequestObjectToSetStatusToCanceled($params=[]) 
    {
        try {
            $request = new LazopRequest('/order/cancel', 'POST');
            if (isset($params["reason_detail"]) and !empty($params["reason_detail"])) {
                $request->addApiParam('reason_detail', $params["reason_detail"]);
            }
            if (isset($params["reason_id"]) and !empty($params["reason_id"])) {
                $request->addApiParam('reason_id', $params["reason_id"]);
            }
            if (isset($params["order_item_id"])) {
                if ($this->isJson($params["order_item_id"])) {
                    $request->addApiParam('order_item_id', $params["order_item_id"]);
                }
            }
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Update "custom_status" to "shipped_to_warehouse".
     * This is done by clicking "Mark As Shipped". This is an intermediary custom status between "READY_TO_SHIP" & "SHIPPED".
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markLazadaOrderAsShippedToWarehouse(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->id) and !empty($request->id)) {
                $order_purchase_id = (int) $request->id;
                $orderPurchase = LazadaOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->order_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.No such order found")
                    ]);
                }

                if ($orderPurchase->status_custom === strtolower(LazadaOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB)
                    and !isset($orderPurchase->mark_as_shipped_at)) {
                    $orderPurchase->status_custom = strtolower(LazadaOrderPurchase::ORDER_STATUS_SHIPPED_TO_WEARHOUSE);
                    $orderPurchase->mark_as_shipped_at = Carbon::now()->format("Y-m-d H:i:s");
                    $orderPurchase->mark_as_shipped_by = Auth::id();
                    $orderPurchase->save();
                    return response()->json([
                        "success"   => true,
                        "message"   => __("translation.Updated status successfully.")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to update status.")
        ]);
    }
    

    /**
     * This is done by clicking "Pick Confirm". This is an intermediary custom status between "READY_TO_SHIP" & "SHIPPED".
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markLazadaOrderAsPickupConfirmed(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->id) and !empty($request->id)) {
                $order_purchase_id = (int) $request->id;
                $orderPurchase = LazadaOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->order_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.No such order found")
                    ]);
                }

                if ($orderPurchase->status_custom === strtolower(LazadaOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB)
                    and !isset($orderPurchase->pickup_confirmed_at)) {
                    $orderPurchase->pickup_confirmed_at = Carbon::now()->format("Y-m-d H:i:s");
                    $orderPurchase->packed_by = Auth::id();
                    $orderPurchase->save();
                    return response()->json([
                        "success"   => true,
                        "message"   => __("translation.Updated status successfully.")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to update status.")
        ]);
    }


    /**
     * Get the status of init for each order from cache.
     * NOTE:
     * "processing" means the order is either in queue or is processing now.
     * "completed" means the job for "init" has successfully exectued.
     * "failed" means the "init" job failed.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLazadaOrdersProcessingNow(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if (isset($request->json_data)) {
                    $ordersn_arr = json_decode($request->json_data);
                    $completed_init_orders = [];
                    $failed_init_orders = [];
                    $seller_id = $this->getLazadaSellerId();
                    foreach ($ordersn_arr as $ordersn) {
                        $cache_val = $this->getLazadaOrderProcessingRelatedInCacheForTrackingInit($ordersn, $seller_id);
                        if (isset($cache_val) and !empty($cache_val) and in_array($cache_val, [
                            "processing", "completed", "falied"
                        ])) {
                            if ($cache_val == "processing") {
                                continue;
                            } else if ($cache_val == "completed") {
                                array_push($completed_init_orders, $ordersn);
                            } else if ($cache_val == "falied") {
                                array_push($failed_init_orders, $ordersn);
                            }
                            $this->removeLazadaOrderProcessingRelatedInCacheForTrackingInit($ordersn, $seller_id);
                        } else {
                            array_push($failed_init_orders, $ordersn);
                        }
                    }
                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "completed" => $completed_init_orders,
                            "failed"    => $failed_init_orders
                        ],
                        "message"   => __("translation.Successfully retrieved info for processing Lazada orders")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }  
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to fetch init info")
        ]);
    }


    /**
     * Get lazada order count for "processing" filter dropdown.
     */
    public function getLazadaOrdersCount(Request $request)
    {
        try {
            $id = isset($request->id)?(int)$request->id:null;
            return response()->json([
                "success"   => true,
                "data"      => [
                    "pending"   => LazadaOrderPurchase::getStatusSchemaCount(LazadaOrderPurchase::ORDER_STATUS_PENDING, $id),
                    "packed"    => LazadaOrderPurchase::getStatusSchemaCount(LazadaOrderPurchase::ORDER_STATUS_PACKED, $id),
                ]
            ]);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }  
        return response()->json([
            "success"   => false,
            "data"      => []
        ]);
    }
}
