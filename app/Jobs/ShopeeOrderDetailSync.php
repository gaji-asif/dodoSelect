<?php

namespace App\Jobs;

use App\Models\ShopeeOrderParamInit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ShopeeOrderPurchase;
use App\Traits\Inventory\AdjustDisplayReservedQtyTrait;
use App\Traits\Inventory\ShopeeInventoryProductsStockUpdateTrait;
use App\Traits\LineBotTrait;
use App\Traits\ShopeeOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ShopeeOrderDetailSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait, LineBotTrait, ShopeeInventoryProductsStockUpdateTrait, AdjustDisplayReservedQtyTrait;
    
    private $page;
    private $per_page;
    private $website_id;
    private $auth_id;
    private $shopee_shop_id;
    private $ordersn_list;
    private $params;
    private $shopee_limit = 50;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $auth_id, $website_id, $ordersn_list=[], $params="all")
    {
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->auth_id = $auth_id;
        $this->website_id = $website_id;
        $this->ordersn_list = $ordersn_list;
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $arr_size = sizeof($this->ordersn_list);
            if ($arr_size==0) {
                Log::debug("Found empty list while trying to sync old \"Shopee\" orders.");
                return;
            }
            if ($arr_size <= $this->shopee_limit) {
                $first_batch_response = $this->getOrderDetailList($this->ordersn_list);
                if (!isset($first_batch_response)) {
                    $this->triggerPushMessage("Failed to start syncing orders from \"Shopee\".");
                    return;
                }
                $first_batch_response = $first_batch_response->getData();
                if (isset($first_batch_response['orders'])) {
                    $this->processOrderDetails($first_batch_response['orders']);
                }
            } else {
                /* For 65 orders first call to this function uses $ordersn_list from 0, 49. */
                $first_batch_response = $this->getOrderDetailList(array_slice($this->ordersn_list, 0, 50));
                if (!isset($first_batch_response)) {
                    $this->triggerPushMessage("Failed to start syncing orders from \"Shopee\".");
                    return;
                }
                $first_batch_response = $first_batch_response->getData();
                if (isset($first_batch_response['orders'])) {
                    $this->processOrderDetails($first_batch_response['orders']);
                }

                /* For 65 orders second call to this function uses $ordersn_list from 50, 64. */
                ShopeeOrderDetailSync::dispatch($this->shopee_shop_id, $this->auth_id, $this->website_id, array_slice($this->ordersn_list, 50, 50), $this->params);
            }
        } catch (\Exception $exception) {
            $this->triggerPushMessage("Failed to start syncing orders from \"Shopee\".");
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get the order details.
     *
     * @return object
     */
    private function getOrderDetailList($ordersn_list)
    {
        try {
            if (isset($this->shopee_shop_id) and !empty($this->shopee_shop_id)) {
                $client = $this->getShopeeClient($this->shopee_shop_id);
                if (!isset($client)) {
                    return null;
                }

                return $client->order->getOrderDetails([
                    'ordersn_list' => (array) $ordersn_list,
                    'timestamp' => time()
                ]);
            }
        } catch (\Exception $exception) {
            $this->triggerPushMessage("Failed to start syncing orders from \"Shopee\". Failed to retrieve json response.");
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Handle each order details.
     *
     * @param array $order_details_arr
     */
    private function processOrderDetails($order_details_arr)
    {
        foreach ($order_details_arr as $order_detail) {
            $this->storeOrderDetailsInDatabase((array)$order_detail);
        }
    }


    /**
     * Store individual order details in database.
     *
     * @param array $order_detail
     */
    private function storeOrderDetailsInDatabase($order_detail)
    {
        $order_id = "";
        try {
            $order_id = $order_detail['ordersn'];
            $status = $order_detail['order_status'];
            $tracking_number = $order_detail['tracking_no'];

            /* Retrieve old Shopee order purchase from db. */
            $oldOrderPurchase = ShopeeOrderPurchase::where('order_id', $order_id)->where('website_id', $this->website_id)->orderBy('created_at', 'desc')->first();

            /* Params used for deciding shipping methods. */
            $orderParamInit = ShopeeOrderParamInit::whereOrdersn($order_id)->first();

            /* Don't process order which had been already "COMPLETED" or "CANCELLED". */
            if (isset($oldOrderPurchase) and in_array($oldOrderPurchase["status"], [
                ShopeeOrderPurchase::ORDER_STATUS_CANCELLED,
                ShopeeOrderPurchase::ORDER_STATUS_COMPLETED
            ])) {
                if (!isset($orderParamInit)) {
                    ShopeeGetParameterForInitForOrder::dispatch($order_id, $this->shopee_shop_id)->delay(now()->addSeconds(1));
                }

                /* Check and remove duplicate entry */
                $this->checkForDuplicateEntries($oldOrderPurchase->id, $order_id);

                /* Check is "status_custom" is correct.s */
                $this->crossMatchCustomStatusIsCorrect($oldOrderPurchase->id);

                return;
            }

            $id = 0;
            if ($this->params == "all") {
                $orderPurchase = new ShopeeOrderPurchase();

                $old_awb_url = "";
                $old_status_custom = "";
                if (isset($oldOrderPurchase)) {
                    /**
                     * NOTE: if "process_start_date", "process_complete_date" and "process_completion_duration" is found
                     * to be null in $oldOrderPurchase for valid statuses(Ex:READY_TO_SHIP, RETRY_SHIP, COMPLETED, CANCELLED),
                     * then there is a good chance that the system has failed to process these infos.
                     */
                    $orderPurchase->process_start_date = $oldOrderPurchase->process_start_date;
                    $orderPurchase->process_complete_date = $oldOrderPurchase->process_complete_date;
                    $orderPurchase->process_completion_duration = $oldOrderPurchase->process_completion_duration;

                    /* Airway bill */
                    $orderPurchase->awb_printed_at = $oldOrderPurchase->awb_printed_at;
                    $old_awb_url = $oldOrderPurchase->awb_url;
                    $orderPurchase->awb_url = $old_awb_url;
                    $orderPurchase->downloaded_at = $oldOrderPurchase->downloaded_at;

                    /* Date "init" was made for this order. */
                    $orderPurchase->shipped_on_date = $oldOrderPurchase->shipped_on_date;

                    /* The date the order was "Marked As Shipped". */
                    $orderPurchase->mark_as_shipped_at = $oldOrderPurchase->mark_as_shipped_at;

                    /* The selected time ("time_text") for "pickup". */
                    $orderPurchase->pickup_shipped_on = $oldOrderPurchase->pickup_shipped_on;

                    /* Selected shipping method and params for shopee */
                    $orderPurchase->shopee_shipping_method = $oldOrderPurchase->shopee_shipping_method;
                    $orderPurchase->shopee_shipping_method_params = $oldOrderPurchase->shopee_shipping_method_params;

                    $old_status_custom = isset($oldOrderPurchase->status_custom)?$oldOrderPurchase->status_custom:"";

                    /* Delete old Shopee order purchase. */
                    ShopeeOrderPurchase::where('order_id', $order_id)->where('website_id', $this->website_id)->delete();
                }

                /* No "billing_address" is sent in the api response. So using "shipping_addres" as "billing_address". */
                $shipping = (isset($order_detail['recipient_address']) and !empty($order_detail['recipient_address']))?$order_detail['recipient_address']:"";
                $billing = $shipping;

                $shipping_lines_arr = [];
                /* Shipping line options available from the shopee api doc. */
                if (isset($order_detail['shipping_carrier'])) {
                    $shipping_lines_arr['shipping_carrier'] = $order_detail['shipping_carrier'];
                }
                if (isset($order_detail['checkout_shipping_carrier'])) {
                    $shipping_lines_arr['checkout_shipping_carrier'] = $order_detail['checkout_shipping_carrier'];
                }

                /* In case some older orders missing the params due to webhook failure. So if already exists in db no need to update. */
                $orderParamInit = ShopeeOrderParamInit::whereOrdersn($order_id)->first();
                /* Update order parameter for init. */
                if (in_array($status, [
                        ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP,
                        ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP
                    ])) {
                    /**
                     * This is done for specifically for status "RETRY_SHIP".
                     * Using this its determind to show "arrange shipment" and "view failed awb" for each order in datatable.
                     */
                    if ($status == ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP) {
                        $orderPurchase->awb_url = null;
                        $orderPurchase->awb_printed_at = null;
                        $orderPurchase->downloaded_at = null;
                        /* This is done to refecth the awb url. */
                        if (!empty($old_awb_url)) {
                            $old_awb_url = "";
                        }
                        /* Assuming old params for shopping method will change. */
                        ShopeeGetParameterForInitForOrder::dispatch($order_id, $this->shopee_shop_id)->delay(now()->addSeconds(1));
                    } else {
                        /* If params are missing for "READY_TO_SHIP". */
                        if (!isset($orderParamInit) and $orderPurchase->status_custom !== strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING)) {
                            ShopeeGetParameterForInitForOrder::dispatch($order_id, $this->shopee_shop_id)->delay(now()->addSeconds(1));
                        }
                    }
                    /**
                     * Update "status_custom".
                     * "shipped_to_warehouse" does not have any logic behind it. Its assigned just by on click.
                     * Thats why the following is done.
                     */
                    if (!empty($old_status_custom) and $old_status_custom == strtolower(ShopeeOrderPurchase::ORDER_STATUS_SHIPPED_TO_WEARHOUSE)) {
                        $orderPurchase->status_custom = $old_status_custom;
                    } else {
                        $orderPurchase->status_custom = ShopeeOrderPurchase::determineStatusCustom($status, $tracking_number);
                    }

                    /**
                     * Get the airway bill url.
                     * NOTE:
                     * Sometimes the awb url may be missing for older orders OR for the new orders might not have been generated yet.
                     * AirwayBill is only fetchable when the order status is under READY_TO_SHIP and RETRY_SHIP.
                     */
                    if (!empty($old_status_custom) and $old_status_custom != strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING) and
                        (!isset($old_awb_url) || empty($old_awb_url))) {
                        ShopeeOrderAirwayBillPrint::dispatch($this->shopee_shop_id, [$order_id])->delay(now()->addSeconds(5));
                    }
                } else {
                    /* Update "status_custom". */
                    $orderPurchase->status_custom = ShopeeOrderPurchase::determineStatusCustom($status, $tracking_number);

                    /**
                     * Assuming old params for shopping method won't change.
                     * Also no need to fetch init related data for the following statuses.
                     */
                    if (!isset($orderParamInit) and !in_array($status, [
                        ShopeeOrderPurchase::ORDER_STATUS_IN_CANCEL,
                        ShopeeOrderPurchase::ORDER_STATUS_TO_RETURN,
                        ShopeeOrderPurchase::ORDER_STATUS_TO_CONFIRM_RECEIVE,
                        ShopeeOrderPurchase::ORDER_STATUS_UNPAID,
                        ShopeeOrderPurchase::ORDER_STATUS_INVOICE_PENDING
                    ])) {
                        ShopeeGetParameterForInitForOrder::dispatch($order_id, $this->shopee_shop_id)->delay(now()->addSeconds(5));
                    }
                }

                $orderPurchase->website_id = $this->website_id;
                $orderPurchase->order_id = $order_id;
                $orderPurchase->product_id = isset($order_detail['product_id'])?(int)$order_detail['product_id']:0;
                $orderPurchase->seller_id = $this->auth_id;
                $orderPurchase->tracking_number = $tracking_number;
                $orderPurchase->status = $status;
                $orderPurchase->billing = json_encode($billing);
                $orderPurchase->shipping = json_encode($shipping);
                $orderPurchase->line_items = isset($order_detail['items'])?json_encode($order_detail['items']):"";
                $orderPurchase->shipping_lines = !empty($shipping_lines_arr)?json_encode($shipping_lines_arr):"";
                $orderPurchase->currency_symbol = isset($order_detail['currency'])?$order_detail['currency']:NULL;
                $orderPurchase->payment_method = isset($order_detail['payment_method'])?$order_detail['payment_method']:"";
                $orderPurchase->payment_method_title = isset($order_detail['payment_method'])?$order_detail['payment_method']:"";
                $orderPurchase->total = isset($order_detail['total_amount'])?$order_detail['total_amount']:0;
                $orderPurchase->order_date = isset($order_detail['create_time'])?date("Y-m-d H:i:s", $order_detail['create_time']):NULL;

                /* Store the new one. */
                $orderPurchase->save();

                $id = $orderPurchase->id;

                /* Update inventory quantity */
                if ($orderPurchase->status_custom == strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING)) {
                    if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($orderPurchase->order_id, $orderPurchase->website_id, $this->getTagForShopeePlatform())) {
                        $this->initInventoryQtyUpdateForShopee($orderPurchase);
                    }
                } else {
                    /**
                     * Update "display_reserved_qty" for the dodo products in this order.
                     * NOTE:
                     * This will be triggered for any other status/status_custom other than "processing".
                     */
                    if ($this->checkIfDisplayReservedQtyShouldBeUpdated($orderPurchase->order_id, $orderPurchase->website_id, $this->getTagForShopeePlatform())) {
                        AdjustDisplayReservedQty::dispatch($orderPurchase->order_id, $orderPurchase->website_id, $this->getTagForShopeePlatform())->delay(Carbon::now()->addSeconds(2));
                    }
                }
            } else if ($this->params == "tracking_number") {
                if (isset($oldOrderPurchase) and isset($tracking_number) and !empty($tracking_number)) {
                    /* "process_start_time" will be used to calculate the duration of the whole process. */
                    if (!isset($oldOrderPurchase->tracking_number) || empty($oldOrderPurchase->tracking_number)) {
                        $oldOrderPurchase->process_start_date = date("Y-m-d H:i:s", time());
                    }
                    $oldOrderPurchase->status_custom = strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);
                    $oldOrderPurchase->tracking_number = $tracking_number;
                    $oldOrderPurchase->save();
                    /* Get the "awb_url" from Shopee. */
                    if (!isset($oldOrderPurchase->awb_url) || empty($oldOrderPurchase->awb_url)) {
                        ShopeeOrderAirwayBillPrint::dispatch($this->shopee_shop_id, [$order_id])->delay(now()->addSeconds(1));
                    }
                    $id = $oldOrderPurchase->id;
                }
            }

            /**
             * Check and remove duplicate entry
             */
            $this->checkForDuplicateEntries($id, $order_id);
        } catch (\Exception $exception) {
            $this->triggerPushMessage("Failed to store order($order_id) from \"Shopee\" in database while syncing.");
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->website_id}"
        ];
    }
}
