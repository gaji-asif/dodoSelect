<?php

namespace App\Http\Controllers;

use App\Jobs\ShopeeOrderDetailSync;
use App\Jobs\ShopeeOrderSync;
use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use App\Traits\LineBotTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\ShopeeOrderSyncTrait;
use Illuminate\Support\Facades\Cache;

class ShopeeOrderSyncController extends Controller
{
    use ShopeeOrderSyncTrait, LineBotTrait;

    private $bulk_syncing_minutes_limit;


    public function __construct()
    {
        $this->middleware('auth');
        $this->bulk_syncing_minutes_limit = $this->getBulkSyncAllocatedTimeInMinutes();
    }


    /**
     * Start syncing order related data of the selected website from "Shopee".
     * NOTE:
     * -1 means to fetch all order data. The data for a specific date range(from current date to past 15 days).
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderSyncData(Request $request)
    {
        try {
            if (isset($request->website_ids, $request->number_of_orders) and
                !empty($request->website_ids) and !empty($request->number_of_orders)) {
                $website_ids = $request->website_ids;
                $number_of_orders = is_numeric($request->number_of_orders)?(int)$request->number_of_orders:-1;
                if ($number_of_orders < -1 || $number_of_orders == 0) {
                    $number_of_orders = -1;
                }
                $sync_message = "";
                if ($this->isJson($website_ids)) {
                    $website_ids = json_decode($request->website_ids);
    
                    foreach ($website_ids as $index => $website_id) {
                        $shopee_shop = Shopee::find($website_id);
                        if (isset($shopee_shop,$shopee_shop->shop_id) and !empty($shopee_shop->shop_id)) {
                            if(!$this->checkCanBulkSync($shopee_shop->id, Auth::id())) {
                                $sync_message .= "\"".$shopee_shop->shop_name."\", ";
                                continue;
                            }
                            Cache::put($this->getBulkSyncStartTimeCacheKey($shopee_shop->id, Auth::id()), Carbon::now()->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForShopeeOrderBulkSync()));
                            Cache::put($this->getBulkSyncEndTimeCacheKey($shopee_shop->id, Auth::id()), Carbon::now()->addMinutes($this->bulk_syncing_minutes_limit)->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForShopeeOrderBulkSync()));
                            ShopeeOrderSync::dispatch(0, 100, (int) $website_id, (int) $shopee_shop->shop_id, Auth::id(), $number_of_orders)->delay(now()->addSeconds($index*15));
                        }
                    }

                    $message = __("shopee.order.orders_sync_data.success");
                    if (!empty($sync_message)) {
                        $sync_message = substr($sync_message, 0, strlen($sync_message)-2);
                        $message .= "<br/>".$sync_message." are already being synced."; 
                    }
    
                    return response()->json([
                        "success"   => true,
                        "message"   => $message
                    ]);
                }
            }
        } catch (\Exception $exception) {
            $this->triggerPushMessage("Failed to start syncing orders from \"Shopee\"");
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.orders_sync_data.failed")
        ]);
    }


    /**
     * Check if json.
     */
    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    /**
     * Get shopee shops to be used while bulk syncing in modal.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopeeShopsForBulkSyncingModal(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = [];
                $shops = Shopee::where('seller_id', Auth::id())
                    ->select('id','shop_name','shop_id','code')
                    ->orderBy('shop_name', 'asc')
                    ->get();
                // $statusMainSchema = ShopeeOrderPurchase::getMainStatusSchemaForDatatable();
                // $processingStatuses = implode(",", array_column($statusMainSchema[0]['sub_status'], 'id'));
                foreach($shops as $shop) {
                    // $processing_orders_count = !empty($processingStatuses)?ShopeeOrderPurchase::getMultipleStatusSchemaCount($processingStatuses, $shop["id"]):0;
                    $processing_orders_count = ShopeeOrderPurchase::getVerifiedOrderSchemaCount(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING, $shop["id"]);
                    $datetime = $this->getBulkSyncStartTimeCacheValue($shop->id, Auth::id());
                    if (empty($datetime)) {
                        $datetime = $this->getIntervalWiseBulkSyncStartTimeCacheValue();
                    }
                    array_push($data, [
                        "id"    => $shop->id,
                        "text"  => $shop->shop_name,
                        "html"  => "<span>".$shop->shop_name." ( $processing_orders_count to process )</span><br/><span style='font-size:12px;'>Last Updated: ".Carbon::parse($datetime)->format("d/m/Y h:i A")."</span>"
                    ]);
                }
                return response()->json([
                    "success"   => true,
                    "data"      => $data
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "data"      => []
        ]);
    }


    /**
     * Sync selected orders from Shopee to DB.
     * NOTE:
     * Orders belonging in more then 1 specific website can be asked to be synced.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkSyncSelectedOrders(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (isset($request->json_data)) {
                    $ordersn_arr = [];
                    /* Contains the "shop_id" of a Shopee shop. In request orders from
                     * different shops under different sites can be sent at the same time.
                     */
                    $shopee_shop_id_arr = [];
                    /* Contains the "website_id". In request orders from different sites can be sent at the same time. */
                    $website_id_arr = [];
                    /* Orders to be synced from Shopee to db. */
                    $arr = json_decode($request->json_data);
                    foreach ($arr as $web_order_data) {
                        $order_data = explode("*", $web_order_data);
                        /* $order_data[0] is 'website_id'('id' in 'shopee' table), $order_data[1] is 'id'('shopee_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                        $order_purchase_id = (int) $order_data[1];
                        $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                        if (!isset($orderPurchase, $orderPurchase->website_id)) {
                            continue;
                        }

                        $shopee_shop = Shopee::find($orderPurchase->website_id);
                        if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                            continue;
                        }

                        $website_id = $orderPurchase->website_id;
                        if (!in_array($website_id, $website_id_arr)) {
                            /* $website_id_arr = [4,5,6] */
                            array_push($website_id_arr, $website_id);
                            $ordersn_arr[$website_id] = [];
                        }
                        if (!isset($shopee_shop_id_arr[$website_id])) {
                            /**
                             * $shopee_shop_id_arr[4] = 'k1k2k3'
                             * $shopee_shop_id_arr[5] = 'x8x9x0'
                             */
                            $shopee_shop_id_arr[$website_id] = (int) $shopee_shop->shop_id;
                        }

                        /**
                         * $ordersn_arr[4] = ['abc123', 'mno326']
                         * $ordersn_arr[5] = ['xyz890', 'ndf111', 'rqe920']
                         */
                        array_push($ordersn_arr[$website_id], $orderPurchase->order_id);
                    }
                    if (sizeof($website_id_arr) > 0) {
                        /* Loop through each of the website. */
                        foreach($website_id_arr as $website_id) {
                            /* Get the "ordersn" for the specific website. Ex: for 4; $ordersn_arr[4] = ['abc123', 'mno326'] */
                            $ordersn_list = $ordersn_arr[$website_id];
                            /* Get the "shop_id" for the specific website. Ex: $shopee_shop_id_arr[4] = 'k1k2k3' */
                            $shopee_shop_id = $shopee_shop_id_arr[$website_id];
                            $arr_size = sizeof($ordersn_list);
                            /**
                             * ShopeeOrderDetailSync(Job) can work with 100 emails only.
                             * Ex: For 415 orders, send the 500 orders to sync in a batch of 100 resulting in 5 times
                             * (0-99, 100-199, 200-299, 300-399 & 400-499[400-414]) dispatching the job ShopeeOrderDetailSync.
                             * Here $loop_count determines the loop counter.
                             */
                            if (isset($shopee_shop_id) and $arr_size > 0) {
                                if ($arr_size > 100) {
                                    $loop_count = $arr_size / 100;
                                    if ($arr_size % 100 != 0) {
                                        $loop_count += 1;
                                    }
                                    for ($i=0; $i < $loop_count; $i++) {
                                        ShopeeOrderDetailSync::dispatch($shopee_shop_id, $this->getShopeeSellerId(), $website_id, array_slice($ordersn_list, $i*100, 100))->delay(now()->addSeconds($i*5));
                                    }
                                } else {
                                    ShopeeOrderDetailSync::dispatch($shopee_shop_id, $this->getShopeeSellerId(), $website_id, $ordersn_list)->delay(now()->addSeconds(5));
                                }
                            }
                        }
                        return response()->json([
                            "success"   => true,
                            "message"   => __("shopee.order.bulk_sync_selected_order.success")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.bulk_sync_selected_order.failed")
        ]);
    }


    /**
     * Sync old shopee orders.
     */
    public function syncOldOrdersFromShopee()
    {
        try {
            \App\Jobs\ShopeeSyncOlderOrderPurchaseDetails::dispatch();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
