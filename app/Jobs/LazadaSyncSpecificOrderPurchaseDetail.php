<?php

namespace App\Jobs;

use App\Models\LazadaOrderPurchase;
use App\Traits\Inventory\AdjustDisplayReservedQtyTrait;
use App\Traits\Inventory\LazadaInventoryProductsStockUpdateTrait;
use App\Traits\LazadaOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaSyncSpecificOrderPurchaseDetail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait, LazadaInventoryProductsStockUpdateTrait, AdjustDisplayReservedQtyTrait;

    private $website_id;
    private $order_id;
    private $seller_id;
    private $access_token;
    private $update_cache;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($website_id, $order_id, $seller_id, $access_token, $update_cache=false)
    {
        $this->website_id = (int) $website_id;
        $this->order_id = (int) $order_id;
        $this->seller_id = (int) $seller_id;
        $this->access_token = $access_token;
        $this->update_cache = $update_cache;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->syncSpecificOrderFromLazada([
                "order_id" => $this->order_id,
            ]);

            if ($this->update_cache and Cache::has($this->getKeyPrefixForLazadaTrackingInit($this->seller_id).$this->order_id)) {
                $this->putLazadaOrderProcessingRelatedInCacheForTrackingInit($this->order_id, "completed", $this->seller_id);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function getRequestObjectToGetSpecificOrderFromLazada($params=[])
    {
        try {
            $request = new LazopRequest('/order/get', 'GET');
            $request->addApiParam('order_id', $params['order_id']);
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    private function syncSpecificOrderFromLazada($params)
    {
        try {
            if (isset($params) and !empty($this->access_token)) {
                $client = $this->getLazadaClient();
                $response = $client->execute($this->getRequestObjectToGetSpecificOrderFromLazada($params), $this->access_token);
                if (isset($response) and $this->isJson($response)) {
                    $data = json_decode($response);
                    if (isset($data->data)) {
                        $order_data = (array)$data->data;
                        $this->storeLazadaOrderPurchaseInDatabase((array)$order_data);
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


    private function storeLazadaOrderPurchaseInDatabase($order_data)
    {
        try {
            if (isset($order_data)) {
                $lazada_order_purchase = new LazadaOrderPurchase();
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
                }

                /* Delete old order purchsase for lazada from database */
                LazadaOrderPurchase::whereOrderId($order_data["order_id"])->delete();

                /* Create new lazada order purchase in database */
                $lazada_order_purchase->save();

                /* Update inventory quantity */
                if ($lazada_order_purchase->status_custom == strtolower(LazadaOrderPurchase::ORDER_STATUS_PROCESSING)) {
                    if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($lazada_order_purchase->order_id, $lazada_order_purchase->website_id, $this->getTagForLazadaPlatform())) {
                        $this->initInventoryQtyUpdateForLazada($lazada_order_purchase);
                    }
                } else {
                    /**
                     * Update "display_reserved_qty" for the dodo products in this order.
                     * NOTE:
                     * This will be triggered for any other status/status_custom other than "processing".
                     */
                    if ($this->checkIfDisplayReservedQtyShouldBeUpdated($lazada_order_purchase->order_id, $lazada_order_purchase->website_id, $this->getTagForLazadaPlatform())) {
                        AdjustDisplayReservedQty::dispatch($lazada_order_purchase->order_id, $lazada_order_purchase->website_id, $this->getTagForLazadaPlatform())->delay(Carbon::now()->addSeconds(2));
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
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
