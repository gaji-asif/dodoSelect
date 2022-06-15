<?php

namespace App\Jobs;

use App\Models\Lazada;
use App\Models\LazadaOrderPurchase;
use App\Traits\LazadaOrderPurchaseTrait;
use App\Traits\LazadaOrderSyncTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaOrderAutoSyncEveryInterval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderSyncTrait, LazadaOrderPurchaseTrait;
    private $number_of_orders;
    private $auth_id;
    private $session_time_limit;
    private $access_token_list;
    private $lazada_total_orders_count;
    private $sync_type;
    private $lazada_api_limit = 100;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($number_of_orders=0, $auth_id=0, $sync_type="manual")
    {
        $this->number_of_orders = $number_of_orders<=0?-1:$number_of_orders;
        $this->auth_id = $auth_id;
        $this->sync_type = $sync_type;
        $this->session_time_limit = $this->getBulkSyncAllocatedTimeInMinutes();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $lazada_shops = Lazada::get();
            $sellers = [];
            if ($this->auth_id != 0) {
                array_push($sellers, $this->auth_id);
            } else {
                $sellers = $this->getSellers();
            }
            if (sizeof($sellers) == 0) {
                return;
            }
            $this->getAccessTokenForLazadaShopsFromDatabase();
            $this->setIntervalWiseBulkSyncStartTimeCacheValue();
            foreach ($lazada_shops as $index => $lazada_shop) {
                $access_token = $this->access_token_list[$lazada_shop->id];
                if (!empty($access_token)) {
                    foreach ($sellers as $seller_id) {
                        if ($this->number_of_orders == -1) {
                            $this->setLazadaTotalOrderCount($this->getOrdersTotalCountFromLazada($access_token));
                            $number_of_orders_tmp = $this->getLazadaTotalOrderCount();
                            if ($number_of_orders_tmp == 0) {
                                continue;
                            }
                        }
                        $loop_counter = $number_of_orders_tmp/$this->lazada_api_limit;
                        if ($number_of_orders_tmp>$this->lazada_api_limit and $number_of_orders_tmp%$this->lazada_api_limit > 0) {
                            $loop_counter += 1;
                        }
                        for ($i=0; $i<$loop_counter; $i++) {
                            if ($number_of_orders_tmp < $this->lazada_api_limit) {
                                $end_index = $number_of_orders_tmp;
                            } else {
                                $number_of_orders_tmp -= $this->lazada_api_limit;
                                $end_index = $this->lazada_api_limit;
                            }
                            if ($i == 0) {
                                Cache::put($this->getBulkSyncStartTimeCacheKey($lazada_shop->id, $seller_id), Carbon::now()->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForLazadaOrderBulkSync()));
                                Cache::put($this->getBulkSyncEndTimeCacheKey($lazada_shop->id, $seller_id), Carbon::now()->addMinutes($this->session_time_limit)->format("Y-m-d H:i:s"), Carbon::now()->addHours($this->getCacheExpirationPeriodForLazadaOrderBulkSync()));
                            }
                            LazadaOrderPurchaseSyncOrderDetails::dispatch($lazada_shop->id, $seller_id, $i*$this->lazada_api_limit, $end_index, $access_token, $this->sync_type)->delay(Carbon::now()->addSeconds($i*20));
                        }
                    }
                } else {
                    Log::debug("No access token found.");
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get distinct sellers from "lazada_order_purchases" table.
     */
    private function getSellers() 
    {
        try {
            $sellers = LazadaOrderPurchase::select("seller_id")->distinct()->get();
            $data = [];
            foreach ($sellers as $seller) {
                array_push($data, $seller->seller_id);
            }
            return $data;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get access token from database.
     */
    private function getAccessTokenForLazadaShopsFromDatabase() 
    {
        try {
            $lazada_shops = Lazada::get();
            foreach ($lazada_shops as $lazada_shop) {
                $this->access_token_list[$lazada_shop->id] = $this->getAccessTokenForLazada($lazada_shop->id);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get total orders found in Lazada.
     */
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
     * Get request object for getting orders from Lazada.
     */
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
                if ($this->sync_type == "auto") {
                    $request->addApiParam($params["day_filter_type"], Carbon::now()->subDays(1)->toISOString());
                } else {
                    $request->addApiParam($params["day_filter_type"], Carbon::now()->subDays($params["days"])->toISOString());
                }
            } else {
                if ($this->sync_type == "auto") {
                    $request->addApiParam("update_after", Carbon::now()->subMinutes(90)->toISOString());
                } else {
                    $request->addApiParam("update_after", Carbon::now()->subDays(15)->toISOString());
                }
            }

            if (isset($params["status"]) and !empty($params["status"]) and in_array($params["status"], [
                "pending","shipped", "canceled", "ready_to_ship", "delivered", "returned", "failed", "topack", "toship"
            ])) {
                $request->addApiParam('status', $params["status"]);
            }
            $request->addApiParam('sort_by', 'updated_at');
            $request->addApiParam('sort_direction', 'DESC');
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    public function setLazadaTotalOrderCount($total_count=0) 
    {
        $this->lazada_total_orders_count = $total_count;
    }


    public function getLazadaTotalOrderCount() 
    {
        return $this->lazada_total_orders_count;   
    }
}
