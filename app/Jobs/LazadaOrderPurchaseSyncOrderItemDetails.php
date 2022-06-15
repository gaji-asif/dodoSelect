<?php

namespace App\Jobs;

use App\Models\LazadaOrderPurchase;
use App\Models\LazadaOrderPurchaseItem;
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
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaOrderPurchaseSyncOrderItemDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait, LazadaInventoryProductsStockUpdateTrait, AdjustDisplayReservedQtyTrait;
    
    private $website_id;
    private $order_id;
    private $access_token;
    private $tracking_code;
    private $shipment_provider;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($website_id, $order_id, $access_token)
    {
        $this->website_id = (int) $website_id;
        $this->order_id = (int) $order_id;
        $this->access_token = $access_token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $order_purchase = LazadaOrderPurchase::whereOrderId($this->order_id)->first();
            if (!isset($order_purchase)) {
                return;
            }
            if (isset($order_purchase->status_custom) and in_array($order_purchase->status_custom, [
                LazadaOrderPurchase::ORDER_STATUS_DELIVERED,
                LazadaOrderPurchase::ORDER_STATUS_CANCELLED
            ])) {
                /**
                 * Check if items for these already exists. If yes then just return.
                 */
                $exisiting_items_count = LazadaOrderPurchaseItem::whereOrderId($this->order_id)->count();
                if ($exisiting_items_count > 0) {
                    return;
                }
            }
            $items = [];

            $client = $this->getLazadaClient();
            if (isset($client)) {
                $response = $client->execute($this->getRequestObjectToGetSpecificOrderItemssFromLazada(), $this->access_token);
                if (isset($response) and $this->isJson($response)) {
                    $data = json_decode($response);
                    if (isset($data->data)) {
                        foreach ($data->data as $item) {
                            if (isset($item->order_item_id)) {
                                array_push($items, $item->order_item_id);
                                /* Check if the item already exists in datatbase. */
                                $old_order_purchase_item = LazadaOrderPurchaseItem::whereOrderItemId($item->order_item_id)->latest()->first();
                                if (isset($old_order_purchase_item, $old_order_purchase_item->status)) {
                                    /* Check if the item has already been "cancelled" or "delivered". if yes then ignore any further procedure. */
                                    if (!in_array($old_order_purchase_item->status, [
                                        LazadaOrderPurchase::ORDER_STATUS_CANCELLED,
                                        LazadaOrderPurchase::ORDER_STATUS_DELIVERED
                                    ])) {
                                        /* Create new updated item entry and remove old data. */
                                        $this->storeLazadaOrderPurchaseItemInDatabase((array)$item, true); 
                                    }
                                } else {
                                    /* Create new item. */
                                    $this->storeLazadaOrderPurchaseItemInDatabase((array)$item); 
                                }
                            }
                        }
                    }
                }
                if (sizeof($items) > 0) {
                    $order_purchase->order_item_ids = json_encode($items);
                    if (!in_array($order_purchase->derived_status, [
                            LazadaOrderPurchase::ORDER_STATUS_PENDING,
                            LazadaOrderPurchase::ORDER_STATUS_DELIVERED,
                            LazadaOrderPurchase::ORDER_STATUS_CANCELLED
                        ])) { 
                        if(!isset($order_purchase->tracking_number) || empty($order_purchase->tracking_number)) {
                            $order_purchase->tracking_number = $this->tracking_code;
                        }
                        if(!isset($order_purchase->shipment_provider) || empty($order_purchase->shipment_provider)) {
                            $order_purchase->shipment_provider = $this->shipment_provider;
                        }
                    }
                    $order_purchase->save();

                    /* Update inventory quantity */
                    if ($order_purchase->status_custom == strtolower(LazadaOrderPurchase::ORDER_STATUS_PROCESSING)) {
                        if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($order_purchase->order_id, $order_purchase->website_id, $this->getTagForLazadaPlatform())) {
                            $this->initInventoryQtyUpdateForLazada($order_purchase);
                        }
                    } else {
                        /**
                         * Update "display_reserved_qty" for the dodo products in this order.
                         * NOTE:
                         * This will be triggered for any other status/status_custom other than "processing".
                         */
                        if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order_purchase->order_id, $order_purchase->website_id, $this->getTagForLazadaPlatform())) {
                            AdjustDisplayReservedQty::dispatch($order_purchase->order_id, $order_purchase->website_id, $this->getTagForLazadaPlatform())->delay(Carbon::now()->addSeconds(2));
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function getRequestObjectToGetSpecificOrderItemssFromLazada() 
    {
        try {
            $request = new LazopRequest('/order/items/get', 'GET');
            $request->addApiParam('order_id', $this->order_id);
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }

    
    private function storeLazadaOrderPurchaseItemInDatabase($order_item_data, $delete_old_data=false) 
    {
        try {
            if (isset($order_item_data)) {
                if ($delete_old_data) {
                    /* Remove old time info. */
                    LazadaOrderPurchaseItem::whereOrderItemId($order_item_data["order_item_id"])->delete();
                }

                /* Create new entry. */
                $lazada_order_purchase_item = new LazadaOrderPurchaseItem();
                $lazada_order_purchase_item->order_id = $this->order_id;
                $lazada_order_purchase_item->order_item_id = isset($order_item_data["order_item_id"])?$order_item_data["order_item_id"]:null;
                $lazada_order_purchase_item->website_id = $this->website_id;
                $lazada_order_purchase_item->package_id = isset($order_item_data["package_id"])?$order_item_data["package_id"]:null;
                $lazada_order_purchase_item->invoice_number = isset($order_item_data["invoice_number"])?$order_item_data["invoice_number"]:null;
                $lazada_order_purchase_item->name = isset($order_item_data["name"])?$order_item_data["name"]:"";
                $lazada_order_purchase_item->shop_id = isset($order_item_data["shop_id"])?$order_item_data["shop_id"]:null;
                $lazada_order_purchase_item->purchase_order_number = isset($order_item_data["purchase_order_number"])?$order_item_data["purchase_order_number"]:null;
                $lazada_order_purchase_item->sku = isset($order_item_data["sku"])?$order_item_data["sku"]:null;
                $lazada_order_purchase_item->item_price = isset($order_item_data["item_price"])?floatval($order_item_data["item_price"]):0;
                $lazada_order_purchase_item->paid_price = isset($order_item_data["paid_price"])?floatval($order_item_data["paid_price"]):0;
                $lazada_order_purchase_item->tax_amount = isset($order_item_data["tax_amount"])?floatval($order_item_data["tax_amount"]):0;
                $lazada_order_purchase_item->shipping_fee_original = isset($order_item_data["shipping_fee_original"])?floatval($order_item_data["shipping_fee_original"]):0;
                $lazada_order_purchase_item->variation = isset($order_item_data["variation"])?$order_item_data["variation"]:null;
                $lazada_order_purchase_item->currency = isset($order_item_data["currency"])?$order_item_data["currency"]:null;
                $lazada_order_purchase_item->order_flag = isset($order_item_data["order_flag"])?$order_item_data["order_flag"]:null;
                $lazada_order_purchase_item->shop_sku = isset($order_item_data["shop_sku"])?$order_item_data["shop_sku"]:null;
                $this->tracking_code = isset($order_item_data["tracking_code"])?$order_item_data["tracking_code"]:null;
                $lazada_order_purchase_item->tracking_code = $this->tracking_code;
                $lazada_order_purchase_item->status = isset($order_item_data["status"])?$order_item_data["status"]:null;
                $lazada_order_purchase_item->tracking_code_pre = isset($order_item_data["tracking_code_pre"])?$order_item_data["tracking_code_pre"]:null;
                $lazada_order_purchase_item->is_digital = isset($order_item_data["is_digital"])?(int)$order_item_data["is_digital"]:0;
                $lazada_order_purchase_item->cancel_return_initiator = isset($order_item_data["cancel_return_initiator"])?$order_item_data["cancel_return_initiator"]:null;
                $lazada_order_purchase_item->order_created_at = isset($order_item_data["created_at"])?Carbon::parse($order_item_data["created_at"])->format("Y-m-d h:i:s"):null;
                $lazada_order_purchase_item->order_updated_at = isset($order_item_data["updated_at"])?Carbon::parse($order_item_data["updated_at"])->format("Y-m-d h:i:s"):null;
                $lazada_order_purchase_item->purchase_order_id = isset($order_item_data["purchase_order_id"])?$order_item_data["purchase_order_id"]:null;
                $lazada_order_purchase_item->voucher_platform = isset($order_item_data["voucher_platform"])?$order_item_data["voucher_platform"]:null;
                $lazada_order_purchase_item->voucher_seller = isset($order_item_data["voucher_seller"])?$order_item_data["voucher_seller"]:null;
                $lazada_order_purchase_item->order_type = isset($order_item_data["order_type"])?$order_item_data["order_type"]:null;
                $lazada_order_purchase_item->stage_pay_status = isset($order_item_data["stage_pay_status"])?$order_item_data["stage_pay_status"]:null;
                $lazada_order_purchase_item->warehouse_code = isset($order_item_data["warehouse_code"])?$order_item_data["warehouse_code"]:null;
                $lazada_order_purchase_item->voucher_seller_lpi = isset($order_item_data["voucher_seller_lpi"])?$order_item_data["voucher_seller_lpi"]:null;
                $lazada_order_purchase_item->voucher_platform_lpi = isset($order_item_data["voucher_platform_lpi"])?$order_item_data["voucher_platform_lpi"]:null;
                $lazada_order_purchase_item->buyer_id = isset($order_item_data["buyer_id"])?$order_item_data["buyer_id"]:null;
                $lazada_order_purchase_item->voucher_code = isset($order_item_data["voucher_code"])?$order_item_data["voucher_code"]:null;
                $lazada_order_purchase_item->voucher_code_seller = isset($order_item_data["voucher_code_seller"])?$order_item_data["voucher_code_seller"]:null;
                $lazada_order_purchase_item->voucher_code_platform = isset($order_item_data["voucher_code_platform"])?$order_item_data["voucher_code_platform"]:null;
                $lazada_order_purchase_item->delivery_option_sof = isset($order_item_data["delivery_option_sof"])?$order_item_data["delivery_option_sof"]:null;
                $lazada_order_purchase_item->is_fbl = isset($order_item_data["is_fbl"])?$order_item_data["is_fbl"]:null;
                $lazada_order_purchase_item->is_reroute = isset($order_item_data["is_reroute"])?$order_item_data["is_reroute"]:null;
                $lazada_order_purchase_item->reason = isset($order_item_data["reason"])?$order_item_data["reason"]:null;
                $lazada_order_purchase_item->shipping_fee_discount_seller = isset($order_item_data["shipping_fee_discount_seller"])?floatval($order_item_data["shipping_fee_discount_seller"]):0;
                $lazada_order_purchase_item->shipping_fee_discount_platform = isset($order_item_data["shipping_fee_discount_platform"])?floatval($order_item_data["shipping_fee_discount_platform"]):0;
                $lazada_order_purchase_item->voucher_amount = isset($order_item_data["voucher_amount"])?floatval($order_item_data["voucher_amount"]):0;
                $lazada_order_purchase_item->wallet_credits = isset($order_item_data["wallet_credits"])?floatval($order_item_data["wallet_credits"]):0;
                $lazada_order_purchase_item->shipping_amount = isset($order_item_data["shipping_amount"])?floatval($order_item_data["shipping_amount"]):0;
                $lazada_order_purchase_item->shipping_service_cost = isset($order_item_data["shipping_service_cost"])?(int)$order_item_data["shipping_service_cost"]:0;
                $lazada_order_purchase_item->promised_shipping_time = isset($order_item_data["promised_shipping_time"])?Carbon::parse($order_item_data["promised_shipping_time"])->format("Y-m-d h:i:s"):null;
                $lazada_order_purchase_item->sla_time_stamp = isset($order_item_data["sla_time_stamp"])?Carbon::parse($order_item_data["sla_time_stamp"])->format("Y-m-d h:i:s"):null;
                $lazada_order_purchase_item->digital_delivery_info = isset($order_item_data["digital_delivery_info"])?$order_item_data["digital_delivery_info"]:null;
                $lazada_order_purchase_item->return_status = isset($order_item_data["return_status"])?$order_item_data["return_status"]:null;
                $lazada_order_purchase_item->shipping_type = isset($order_item_data["shipping_type"])?$order_item_data["shipping_type"]:null;
                $this->shipment_provider = isset($order_item_data["shipment_provider"])?$order_item_data["shipment_provider"]:null;
                $lazada_order_purchase_item->shipment_provider = $this->shipment_provider;
                $lazada_order_purchase_item->shipping_provider_type = isset($order_item_data["shipping_provider_type"])?$order_item_data["shipping_provider_type"]:null;
                $lazada_order_purchase_item->product_main_image = isset($order_item_data["product_main_image"])?$order_item_data["product_main_image"]:null;
                $lazada_order_purchase_item->product_detail_url = isset($order_item_data["product_detail_url"])?$order_item_data["product_detail_url"]:null;
                $lazada_order_purchase_item->reason_detail = isset($order_item_data["reason_detail"])?$order_item_data["reason_detail"]:null;
                $lazada_order_purchase_item->extra_attributes = isset($order_item_data["extra_attributes"])?$order_item_data["extra_attributes"]:null;
                $lazada_order_purchase_item->save();
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
