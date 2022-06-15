<?php

namespace App\Jobs;

use App\Models\Lazada;
use App\Models\LazadaOrderPurchase;
use App\Models\LazadaOrderPurchaseItem;
use App\Traits\LazadaOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaOrderPurchaseSyncOrderDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait;

    private $website_id;
    private $seller_id;
    private $offset;
    private $limit;
    private $access_token;
    private $sync_type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($website_id, $seller_id, $offset, $limit, $access_token, $sync_type="manual")
    {
        $this->website_id = $website_id;
        $this->seller_id = $seller_id;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->access_token = $access_token;
        $this->sync_type = $sync_type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->syncOrdersInBulkFromLazada([
                "offset" => $this->offset,
                "limit"  => $this->limit
            ]);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
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
                $request->addApiParam('limit', 100);
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
                    $request->addApiParam("update_after", Carbon::now()->subHours(4)->toISOString());
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


    private function syncOrdersInBulkFromLazada($params) 
    {
        try {
            if (isset($params) and !empty($this->access_token)) {
                $client = $this->getLazadaClient();
                $response = $client->execute($this->getRequestObjectToGetOrdersFromLazada($params), $this->access_token);
                if (isset($response) and $this->isJson($response)) {
                    $data = json_decode($response);
                    if (isset($data->data, $data->data->orders)) {
                        foreach ($data->data->orders as $index => $order_data) {
                            $order_data = (array)$order_data;
                            if (isset($order_data["order_id"]) and $this->checkIfOrderShouldBeStored($order_data["order_id"])) {
                                $this->storeLazadaOrderPurchaseInDatabase((array)$order_data);
                                /* Create new item. */
                                LazadaOrderPurchaseSyncOrderItemDetails::dispatch($this->website_id, $order_data["order_id"], $this->access_token)->delay(Carbon::now()->addSeconds($index*5));
                            } else {
                                Log::debug("Failed to store order purchase info for Lazada (website_id : ".$this->website_id.").");
                            }
                        }
                    } else {
                        Log::debug("No orders found in response for Lazada (website_id : ".$this->website_id.").");
                    }
                } else {
                    Log::debug("Invalid response for Lazada (website_id : ".$this->website_id.").");
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function checkIfOrderShouldBeStored($order_id)
    {
        try {
            $order_purchase = LazadaOrderPurchase::whereOrderId($order_id)->first();
            if (!isset($order_purchase)) {
                return true;
            }
            /* The order has already been "cancelled" or "delivered" than nothing to update. */
            if (isset($order_purchase->status_custom) and in_array($order_purchase->status_custom, [
                LazadaOrderPurchase::ORDER_STATUS_CANCELLED,
                LazadaOrderPurchase::ORDER_STATUS_DELIVERED
            ])) {
                /**
                 * Check if items for these already exists. If yes then just return.
                 */
                $exisiting_items_count = LazadaOrderPurchaseItem::whereOrderId($this->order_id)->count();
                if ($exisiting_items_count > 0) {
                    return false;
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return true;
    }


    private function storeLazadaOrderPurchaseInDatabase($order_data) 
    {
        try {
            if (isset($order_data)) {
                $lazada_order_purchase = new LazadaOrderPurchase();
                $old_lazada_order_purchase = LazadaOrderPurchase::whereOrderId($order_data["order_id"])->latest()->first();
                if (isset($old_lazada_order_purchase)) {
                    $lazada_order_purchase->tracking_number = $old_lazada_order_purchase->tracking_number;
                    $lazada_order_purchase->order_item_ids = $old_lazada_order_purchase->order_item_ids;
                    $lazada_order_purchase->awb_printed_at = $old_lazada_order_purchase->awb_printed_at;
                    $lazada_order_purchase->downloaded_at = $old_lazada_order_purchase->downloaded_at;
                    $lazada_order_purchase->shipped_on_date = $old_lazada_order_purchase->shipped_on_date;
                    $lazada_order_purchase->mark_as_shipped_at = $old_lazada_order_purchase->mark_as_shipped_at;
                    $lazada_order_purchase->pickup_confirmed_at = $old_lazada_order_purchase->pickup_confirmed_at;
                    $lazada_order_purchase->process_start_date = $old_lazada_order_purchase->process_start_date;
                    $lazada_order_purchase->process_complete_date = $old_lazada_order_purchase->process_complete_date;
                    $lazada_order_purchase->process_completion_duration = $old_lazada_order_purchase->process_completion_duration;
                    $lazada_order_purchase->warehouse_code = $old_lazada_order_purchase->warehouse_code;
                    $lazada_order_purchase->package_id = $old_lazada_order_purchase->package_id;
                    $lazada_order_purchase->shipment_provider = $old_lazada_order_purchase->shipment_provider;
                    
                    /* Remove old time info. */
                    LazadaOrderPurchase::whereOrderId($order_data["order_id"])->delete();
                }
                $lazada_order_purchase->website_id = $this->website_id;
                $lazada_order_purchase->seller_id = $this->seller_id;
                $lazada_order_purchase->order_id = $order_data["order_id"];
                $lazada_order_purchase->order_number = $order_data["order_number"];
                $lazada_order_purchase->order_date = isset($order_data["created_at"])?Carbon::parse($order_data["created_at"])->format("Y-m-d h:i:s"):null;
                if (isset($order_data["statuses"])) {
                    $lazada_order_purchase->statuses = json_encode($order_data["statuses"]);
                    $lazada_order_purchase->status_custom = LazadaOrderPurchase::determineStatusCustom($order_data["statuses"]);
                    $lazada_order_purchase->derived_status = LazadaOrderPurchase::getDerivedStatus($order_data["statuses"]);
                }

                $lazada_order_purchase->price = $order_data["price"];
                $lazada_order_purchase->items_count = $order_data["items_count"];
                $lazada_order_purchase->branch_number = Crypt::encrypt($order_data["branch_number"]);
                $lazada_order_purchase->tax_code = Crypt::encrypt($order_data["tax_code"]);
                $lazada_order_purchase->payment_method = Crypt::encrypt($order_data["payment_method"]);
                $lazada_order_purchase->payment_method_title = Crypt::encrypt($order_data["payment_method"]);
                $lazada_order_purchase->customer_first_name = Crypt::encrypt($order_data["customer_first_name"]);
                $lazada_order_purchase->customer_last_name = Crypt::encrypt($order_data["customer_last_name"]);
                $lazada_order_purchase->national_registration_number = Crypt::encrypt($order_data["national_registration_number"]);
                $lazada_order_purchase->billing = isset($order_data["address_billing"])?Crypt::encrypt(json_encode($order_data["address_billing"])):null;
                $lazada_order_purchase->shipping = isset($order_data["address_shipping"])?Crypt::encrypt(json_encode($order_data["address_shipping"])):null;
                $lazada_order_purchase->delivery_type = isset($order_data["delivery_type"])?$order_data["delivery_type"]:"dropship";
                $lazada_order_purchase->remarks = Crypt::encrypt($order_data["remarks"]);
                $lazada_order_purchase->awb_document = isset($order_data["awb_document"])?json_encode($order_data["awb_document"]):null;
                $lazada_order_purchase->address_updated_at = isset($order_data["address_updated_at"])?$order_data["address_updated_at"]:null;
                $lazada_order_purchase->voucher = Crypt::encrypt($order_data["voucher"]);
                $lazada_order_purchase->voucher_code = Crypt::encrypt($order_data["voucher_code"]);
                $lazada_order_purchase->voucher_seller = Crypt::encrypt($order_data["voucher_seller"]);
                $lazada_order_purchase->voucher_platform = Crypt::encrypt($order_data["voucher_platform"]);
                $lazada_order_purchase->extra_attributes = isset($order_data["extra_attributes"])?$order_data["extra_attributes"]:null;
                $lazada_order_purchase->shipping_fee = $order_data["shipping_fee"];
                $lazada_order_purchase->shipping_fee_original = $order_data["shipping_fee_original"];
                $lazada_order_purchase->shipping_fee_discount_seller = $order_data["shipping_fee_discount_seller"];
                $lazada_order_purchase->shipping_fee_discount_platform = $order_data["shipping_fee_discount_platform"];
                $lazada_order_purchase->promised_shipping_times = $order_data["promised_shipping_times"];
                $lazada_order_purchase->gift_option = $order_data["gift_option"];
                $lazada_order_purchase->gift_message = isset($order_data["gift_message"])?Crypt::encrypt($order_data["gift_message"]):NULL;
                $lazada_order_purchase->updated_at = isset($order_data["updated_at"])?Carbon::parse($order_data["updated_at"])->format("Y-m-d h:i:s"):null;
                $lazada_order_purchase->delivery_info = isset($order_data["delivery_info"])?Crypt::encrypt($order_data["delivery_info"]):NULL;
                $lazada_order_purchase->save();
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
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
