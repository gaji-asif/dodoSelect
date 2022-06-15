<?php

namespace App\Http\Controllers;

use App\Jobs\LazadaOrderPurchaseSyncOrderDetails;
use App\Models\Lazada;
use Carbon\Carbon;
use App\Models\LazadaOrderPurchase;
use Lazada\LazopRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\LazadaOrderPurchaseTrait;
use App\Traits\LazadaOrderSyncTrait;
use Illuminate\Support\Facades\Cache;

class LazadaOrderSyncController extends Controller
{
    use LazadaOrderPurchaseTrait, LazadaOrderSyncTrait;

    private $lazada_total_orders_count;
    private $bulk_syncing_minutes_limit;

    public function __construct()
    {
        $this->middleware('auth');
        $this->bulk_syncing_minutes_limit = $this->getBulkSyncAllocatedTimeInMinutes();
    }


    public function setLazadaTotalOrderCount($total_count=0)
    {
        $this->lazada_total_orders_count = $total_count;
    }


    public function getLazadaTotalOrderCount()
    {
        return $this->lazada_total_orders_count;
    }


    public function getOrderSyncData(Request $request)
    {
        try {
            if (isset($request->website_ids, $request->number_of_orders) and
                !empty($request->website_ids) and !empty($request->number_of_orders)) {
                $website_ids = $request->website_ids;
                $number_of_orders = is_numeric($request->number_of_orders)?(int)$request->number_of_orders:-1;
                $sync_message = "";
                if ($number_of_orders < -1 || $number_of_orders == 0) {
                    $number_of_orders = -1;
                }

                $lazada_websites = [];
                if ($this->isJson($website_ids)) {
                    $website_ids = json_decode($request->website_ids);
                    $websites = Lazada::select("id", "shop_name")->whereIn("id", $website_ids)->get();
                    foreach($websites as $website) {
                        $lazada_websites[$website->id] = $website->shop_name;
                    }
                } else {
                    $lazada_websites = Lazada::pluck("shop_name", "id");
                }

                if (sizeof($lazada_websites) > 0) {
                    $already_being_synced_count = 0;
                    $api_limit = 100;
                    foreach ($lazada_websites as $website_id => $shop_name) {
                        $number_of_orders_tmp = $number_of_orders;
                        if(!$this->checkCanBulkSync($website_id, $this->getLazadaSellerId())) {
                            $sync_message .= "\"".$shop_name."\", ";
                            $already_being_synced_count += 1;
                            continue;
                        }
                        $access_token = $this->getAccessTokenForLazada($website_id);
                        if (!empty($access_token)) {
                            if ($number_of_orders == -1) {
                                $this->setLazadaTotalOrderCount($this->getOrdersTotalCountFromLazada($access_token));
                                $number_of_orders_tmp = $this->getLazadaTotalOrderCount();
                                if ($number_of_orders_tmp == 0) {
                                    Cache::put($this->getBulkSyncStartTimeCacheKey($website_id, $this->getLazadaSellerId()), Carbon::now()->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForLazadaOrderBulkSync()));
                                    Cache::put($this->getBulkSyncEndTimeCacheKey($website_id, $this->getLazadaSellerId()), Carbon::now()->addMinutes($this->bulk_syncing_minutes_limit)->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForLazadaOrderBulkSync()));
                                    continue;
                                }
                            }
                            $loop_counter = $number_of_orders_tmp/$api_limit;
                            if ($number_of_orders_tmp>$api_limit and $number_of_orders_tmp%$api_limit > 0) {
                                $loop_counter += 1;
                            }
                            for ($i=0; $i<$loop_counter; $i++) {
                                if ($number_of_orders_tmp < $api_limit) {
                                    $end_index = $number_of_orders_tmp;
                                } else {
                                    $number_of_orders_tmp -= $api_limit;
                                    $end_index = $api_limit;
                                }
                                if ($i == 0) {
                                    Cache::put($this->getBulkSyncStartTimeCacheKey($website_id, $this->getLazadaSellerId()), Carbon::now()->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForLazadaOrderBulkSync()));
                                    Cache::put($this->getBulkSyncEndTimeCacheKey($website_id, $this->getLazadaSellerId()), Carbon::now()->addMinutes($this->bulk_syncing_minutes_limit)->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForLazadaOrderBulkSync()));
                                }
                                LazadaOrderPurchaseSyncOrderDetails::dispatch($website_id, $this->getLazadaSellerId(), $i*$api_limit, $end_index, $access_token)->delay(Carbon::now()->addSeconds($i*60));
                            }
                        } else {
                            Log::debug("No access token found for \"$shop_name\".");
                        }
                    }
                    $message = "";
                    if ($already_being_synced_count == sizeof($lazada_websites) and !empty($sync_message)) {
                        $message = substr($sync_message, 0, strlen($sync_message)-2)." is already being synced.";
                    } else {
                        $message = "Successfully started order syncing from Lazada.";
                        if (!empty($sync_message)) {
                            $message .= " ".substr($sync_message, 0, strlen($sync_message)-2)." is already being synced.";
                        }
                    }
                    return response()->json([
                        "success"   => true,
                        "message"   => $message
                    ]);
                } else {
                    Log::debug("No lazada websites found to sync orders.");
                }
            } else {
                Log::debug("Failed to even start the process to start order syncing from \"Lazada\".");
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Falied to start order syncing from \"Lazada\".")
        ]);
    }


    private function getRequestObjectToGetOrdersFromLazada($params=[])
    {
        try {
            $request = new LazopRequest('/orders/get', 'GET');
            if (isset($params["offset"])) {
                $request->addApiParam('offset', $params["offset"]);
            } else {
                $request->addApiParam('offset', 0);
            }

            if (isset($params["limit"])) {
                $request->addApiParam('limit', $params["limit"]);
            } else {
                $request->addApiParam('limit', 365);
            }

            if (isset($params["day_filter_type"], $params["days"]) and !empty($params["day_filter_type"]) and !empty($params["days"]) and
                in_array($params["day_filter_type"], [
                    "update_after", "update_before", "create_after", "create_before"
                ])) {
                $request->addApiParam($params["day_filter_type"], Carbon::now()->subDays($params["days"])->toISOString());
            } else {
                $request->addApiParam("update_after", Carbon::now()->subDays(15)->toISOString());
            }

            if (isset($params["status"]) and !empty($status) and in_array($status, [
                "pending","shipped", "canceled", "ready_to_ship", "delivered", "returned", "failed", "topack", "toship"
            ])) {
                $request->addApiParam('status', $status);
            }
            $request->addApiParam('sort_by', 'updated_at');
            $request->addApiParam('sort_direction', 'DESC');
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    private function getOrdersTotalCountFromLazada($access_token="")
    {
        try {
            if (!empty($access_token)) {
                $client = $this->getLazadaClient();
                if (isset($client)) {
                    $response = $client->execute($this->getRequestObjectToGetOrdersFromLazada([
                        "offset" => 0,
                        "limit"  => 1
                    ]), $access_token);
                    if (isset($response) and $this->isJson($response)) {
                        $data = json_decode($response);
                        if (isset($data->data, $data->data->countTotal)) {
                            return $data->data->countTotal;
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return 0;
    }


    /**
     * Get shopee shops to be used while bulk syncing in modal.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLazadaShopsForBulkSyncingModal(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = [];
                $shops = Lazada::where('seller_id', $this->getLazadaSellerId())
                    ->select('id','shop_name','shop_id','code')
                    ->orderBy('shop_name', 'asc')
                    ->get();
                $statusMainSchema = LazadaOrderPurchase::getMainStatusSchemaForDatatable();
                $processingStatuses = implode(",", array_column($statusMainSchema[0]['sub_status'], 'id'));
                foreach($shops as $shop) {
                    $processing_orders_count = !empty($processingStatuses)?LazadaOrderPurchase::getMultipleStatusSchemaCount($processingStatuses, $shop["id"]):0;
                    $datetime = $this->getBulkSyncStartTimeCacheValue($shop->id, $this->getLazadaSellerId());
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
     * Sync old shopee orders.
     */
    public function syncOldOrdersFromLazada()
    {
        try {
            \App\Jobs\LazadaSyncOlderOrderPurchaseDetails::dispatch();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
