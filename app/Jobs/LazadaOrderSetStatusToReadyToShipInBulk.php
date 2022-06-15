<?php

namespace App\Jobs;

use App\Models\LazadaOrderPurchase;
use App\Models\LazadaOrderPurchaseItem;
use App\Traits\LazadaOrderPurchaseTrait;
use App\Traits\LazadaOrderSyncTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaOrderSetStatusToReadyToShipInBulk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait, LazadaOrderSyncTrait;
    private $ordersn_list;
    private $shipping_provider;
    private $delivery_type;
    private $access_token;
    private $auth_id;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($access_token, $ordersn_list, $auth_id)
    {
        $this->access_token = $access_token;
        $this->ordersn_list = $ordersn_list;
        $this->auth_id = $auth_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (sizeof($this->ordersn_list) == 0 || empty($this->access_token)) {
                return;
            }
            $client = $this->getLazadaClient();
            foreach ($this->ordersn_list as $order_id) {
                $order_purchase = LazadaOrderPurchase::whereOrderId($order_id)->first();
                if (!isset($order_purchase, $order_purchase->website_id, $order_purchase->order_item_ids)) {
                    Log::debug("Lazada order($order_id) is invalid.");
                    continue;
                }
                if (!isset($order_purchase->order_item_ids) || empty($order_purchase->order_item_ids)) {
                    Log::debug("\"order_item_ids\" is missing for order($order_id) in Lazada.");
                    continue;
                }
                if (!isset($order_purchase->tracking_number) || empty($order_purchase->tracking_number)) {
                    Log::debug("Tracking number is missing for order($order_id) in Lazada.");
                    $this->putLazadaOrderProcessingRelatedInCacheForTrackingInit($order_id, "failed", $this->auth_id);
                    continue;
                }
                $obj = $this->getRequestObjectToSetStatusToReadyToShip([
                    "shipping_provider" => $order_purchase->shipment_provider,
                    "delivery_type"     => $order_purchase->delivery_type,
                    "order_item_ids"    => $order_purchase->order_item_ids,
                    "tracking_number"   => $order_purchase->tracking_number
                ]);
                if (isset($client, $obj)) {
                    $response = $client->execute($obj, $this->access_token);
                    if (isset($response) and $this->isJson($response)) {
                        $data = json_decode($response);
                        if (isset($data->data, $data->data->order_items)) {
                            foreach ($data->data->order_items as $order_item) {
                                $this->updateItemInDatabaseAfterSetStatusToReadyToShip($order_item);
                            }
                            /* Fetch order detail from Lazada */
                            LazadaSyncSpecificOrderPurchaseDetail::dispatch($order_purchase->website_id, $order_purchase->order_id, $this->auth_id, $this->access_token, true)->delay(Carbon::now()->addSeconds(2));
                            /* Fetch order item details from Lazada */
                            LazadaOrderPurchaseSyncOrderItemDetails::dispatch($order_purchase->website_id, $order_purchase->order_id, $this->access_token)->delay(Carbon::now()->addSeconds(2));
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
            $this->clearSessionForFailedJob();
        }               
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


    private function clearSessionForFailedJob()
    {
        try {
            foreach ($this->ordersn_list as $order_id) {
                $cache_val = $this->getLazadaOrderProcessingRelatedInCacheForTrackingInit($order_id, $this->auth_id);
                if (isset($cache_val) and !empty($cache_val)) {
                    if ($cache_val == "processing") {
                        $this->putLazadaOrderProcessingRelatedInCacheForTrackingInit($order_id, "failed", $this->auth_id);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
