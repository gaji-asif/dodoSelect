<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use App\Traits\Inventory\AdjustDisplayReservedQtyTrait;
use App\Traits\Inventory\ShopeeInventoryProductsStockUpdateTrait;
use App\Traits\ShopeeOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ShopeeOrderPurchaseUpdateViaWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait, ShopeeInventoryProductsStockUpdateTrait, AdjustDisplayReservedQtyTrait;

    private $shop_id;
    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shop_id, $data)
    {
        $this->shop_id = $shop_id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $order_purchase_order_id = $this->data["ordersn"];
            $orderPurchase = ShopeeOrderPurchase::whereOrderId($order_purchase_order_id)->first();
            if (!isset($orderPurchase, $orderPurchase->website_id)) {
                Log::debug(__("shopee.order.handle_order_webhook.no_such_order"));
                /* Sync in the order from Shopee. */
                if(!$this->syncSingleMissingOrderFromShopee($this->data["ordersn"], $this->shop_id)) {
                    Log::error(__("shopee.order.handle_order_webhook.sync_job_failed"));
                }
                return;
            }

            $shopee_shop = Shopee::find($orderPurchase->website_id);
            if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                Log::error(__("shopee.order.handle_order_webhook.no_such_shop"));
                return;
            }

            if ((int) $this->shop_id !== (int) $shopee_shop->shop_id) {
                Log::error(__("shopee.order.handle_order_webhook.shop_id_not_match"));
                Log::error('For ordersn('.$this->data["ordersn"].') "shop_id" "'.$this->shop_id.'" was send by Shopee webhook.');
                return;
            }


            /* NOTE: When "tracking_no" is passed no "status" is passed by webhook. */
            if (isset($this->data["tracking_no"]) and !empty($this->data["tracking_no"])) {
                /* "process_start_time" will be used to calculate the duration of the whole process. */
                if (!isset($orderPurchase->tracking_number) || empty($orderPurchase->tracking_number)) {
                    $orderPurchase->process_start_date = date("Y-m-d H:i:s", time());
                    $cached_val = $this->getShopeeOrderProcessingRelatedInCacheForTrackingInit($orderPurchase->order_id);
                    if (isset($cached_val)) {
                        $this->putShopeeOrderProcessingRelatedInCacheForTrackingInit($orderPurchase->order_id, "completed");
                    }
                }
                $orderPurchase->status_custom = strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);
                $orderPurchase->tracking_number = $this->data["tracking_no"];
                $orderPurchase->save();

                /* Check and remove duplicate entry */
                $this->checkForDuplicateEntries($orderPurchase->id, $orderPurchase->order_id);

                /* Get the "awb_url" from Shopee. */
                ShopeeOrderAirwayBillPrint::dispatch((int)$this->shop_id, [$this->data["ordersn"]])->delay(now()->addSeconds(5));
            }

            /**
             * Check if the status send by the webhook is valid.
             * NOTE: When "status" is passed no "tracking_no" is passed by webhook.
             */
            if (isset($this->data["status"]) and !empty($this->data["status"])) {
                if (!array_key_exists($this->data["status"], ShopeeOrderPurchase::getAllOrderStatus())) {
                    Log::error(__("shopee.order.handle_order_webhook.invalid_method"));
                    Log::error('For ordersn('.$this->data["ordersn"].') status "'.$this->data["status"].'" was send by Shopee webhook.');
                    return;
                } else {
                    $orderPurchase->status = $this->data["status"];

                    /* Update order parameter for init. */
                    if (in_array($this->data["status"], [
                        ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP,
                        ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP
                    ])) {
                        ShopeeGetParameterForInitForOrder::dispatch($order_purchase_order_id, $this->shop_id);
                        /**
                         * Update "status_custom" and "process_start_time".
                         * The later will be used to calculate the duration of the whole process.
                         */
                        $tracking_number = "";
                        if (!isset($orderPurchase->tracking_number) || empty($orderPurchase->tracking_number)) {
                            $orderPurchase->process_start_date = date("Y-m-d H:i:s", time());
                            $cached_val = $this->getShopeeOrderProcessingRelatedInCacheForTrackingInit($orderPurchase->order_id);
                            if (isset($cached_val)) {
                                $this->putShopeeOrderProcessingRelatedInCacheForTrackingInit($orderPurchase->order_id, "completed");
                            }
                        } else {
                            $tracking_number = $orderPurchase->tracking_number;
                        }
                        $orderPurchase->status_custom = ShopeeOrderPurchase::determineStatusCustom($this->data["status"], $tracking_number);
                        /**
                         * This is done for specifically for status "RETRY_SHIP".
                         * Using this its determind to show "arrange shipment" and "view failed awb" for each order in datatable.
                         */
                        if ($this->data["status"] == ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP) {
                            $orderPurchase->awb_url = null;
                            $orderPurchase->awb_printed_at = null;
                        } else {
                            if (!empty($tracking_no) and (!isset($orderPurchase->awb_url) || empty($orderPurchase->awb_url))) {
                                /* Get the "awb_url" from Shopee. */
                                ShopeeOrderAirwayBillPrint::dispatch((int)$this->shop_id, [$this->data["ordersn"]])->delay(now()->addSeconds(10));
                            }
                        }
                    } else {
                        if (in_array($this->data["status"], [
                            ShopeeOrderPurchase::ORDER_STATUS_COMPLETED
                        ])) {
                            $process_complete_date = date("Y-m-d H:i:s", time());
                            $orderPurchase->process_complete_date = $process_complete_date;
                            $orderPurchase->process_completion_duration = $this->getShopeeOrderProcessCompletionDuration($orderPurchase->process_start_date, $process_complete_date);
                        }
                        $orderPurchase->status_custom = ShopeeOrderPurchase::determineStatusCustom($this->data["status"], $orderPurchase->tracking_number);
                    }
                    $orderPurchase->save();

                    /* Check and remove duplicate entry */
                    $this->checkForDuplicateEntries($orderPurchase->id, $orderPurchase->order_id);
                    

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
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Sync in the order from shopee which was send in webhook to update some info about
     * the order in our system.
     */
    private function syncSingleMissingOrderFromShopee($ordersn, $shopee_shop_id) {
        try {
            $ordersn_list = [$ordersn];
            $shopee_shop = Shopee::whereShopId($shopee_shop_id)->first();
            if (isset($shopee_shop)) {
                $auth_id = $shopee_shop->seller_id;
                $website_id = $shopee_shop->id;
                ShopeeOrderDetailSync::dispatch($shopee_shop_id, $auth_id, $website_id, $ordersn_list);
                return true;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


    /**
     * Get Shopee order process completion time.
     * NOTE:
     * Process starts when the order has "status" either "READY_TO_SHIP" or "RETRY_SHIP" for the first time and
     * also "tracking_number" is empty.
     * The process ends when the order is completed having status "COMPLETED".
     */
    private function getShopeeOrderProcessCompletionDuration($from_date, $to_date) {
        try {
            if (isset($from_date, $to_date) and !empty($from_date) and !empty($to_date)) {
                $from_date = Carbon::parse($from_date);
                $to_date = Carbon::parse($to_date);
                $days = $from_date->diffInDays($to_date);
                $hours = $from_date->diffInHours($to_date);
                $minutes = $from_date->diffInMinutes($to_date);
                $seconds = $from_date->diffInSeconds($to_date);
                $diff = "";
                if ($days > 0) {
                    $diff .= $days.($days>1?" days":" day");
                    $hours = $hours - 24 * $days;
                }
                if ($hours > 0) {
                    $diff .= " ".$hours.($hours>1?" hours":" hour");
                    $minutes = $minutes - (($days * 24 + $hours) * 60);
                }
                if ($minutes > 0) {
                    $diff .= " ".$minutes.($minutes > 1?" minutes":" minute");
                    $seconds = $seconds - (($days * 24 * 60 + $hours * 60 + $minutes) * 60);
                }
                if ($seconds > 0) {
                    $diff .= " ".$seconds.($seconds > 1?" seconds":" second");
                }
                return $diff;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->shop_id}"
        ];
    }
}
