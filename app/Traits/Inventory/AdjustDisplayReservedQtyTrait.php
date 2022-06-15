<?php

namespace App\Traits\Inventory;

use App\Jobs\AdjustDisplayReservedQty;
use App\Jobs\HandleNewProcessedLogForSpecificOrderJob;
use App\Models\InventoryProductsReservedQuantityLog;
use App\Models\InventoryProductsStockUpdate;
use App\Models\LazadaOrderPurchase;
use App\Models\ProductMainStock;
use App\Models\ShopeeOrderPurchase;
use App\Models\WooOrderPurchase;
use Illuminate\Support\Facades\Log;
use App\Traits\Inventory\PlatformTrait;
use Carbon\Carbon;

trait AdjustDisplayReservedQtyTrait
{
    use PlatformTrait;

    private $order_id;
    private $website_id;
    private $for;
    private $offset;
    private $bulk_limit;
    private $data_processed_order_ids;
    private $data_processing_order_ids;


    /**
     * If $order_id provided then only the products for the specific order will be handled and 
     * for the products(dodo_products) "display_reserved_qty" will be updated(deducted).
     * If not then all the orders will be taken into consideration and the "display_reserved_qty" will be replaced.
     * 
     * @param integer|string $order_id
     * @param integer $website_id
     * @param string $for
     * @param integer $offset
     */
    private function setVariablesForAdjustDisplayReservedQtyTrait($order_id=null, $website_id=null, $for="", $offset=0)
    {
        $this->order_id = $order_id;
        $this->website_id = $website_id;
        $this->for = $for;
        $this->offset = $offset;

        $this->data_processing_order_ids[$this->getTagForShopeePlatform()] = [];
        $this->data_processing_order_ids[$this->getTagForLazadaPlatform()] = [];
        $this->data_processing_order_ids[$this->getTagForWooCommercePlatform()] = [];

        $this->data_processed_order_ids[$this->getTagForShopeePlatform()] = [];
        $this->data_processed_order_ids[$this->getTagForLazadaPlatform()] = [];
        $this->data_processed_order_ids[$this->getTagForWooCommercePlatform()] = [];

        $this->bulk_limit = 300;
    }


    /**
     * This is done for bulk processing and to avoid timeout by queue.
     */
    private function initAdjustDisplayReservedQtyTraitForBulk()
    {
        if (!isset($this->order_id)) {
            $total_reserved_quantity_logs = InventoryProductsReservedQuantityLog::whereStatus("processing")->count();
            $new_offset = $this->bulk_limit + $this->offset;
            if ($total_reserved_quantity_logs > $new_offset) {
                AdjustDisplayReservedQty::dispatch(null, null, "", $new_offset)->delay(Carbon::now()->addSeconds(60));
            }
        }
    }


    private function initAdjustDisplayReservedQtyTrait()
    {
        try {
            /* Get the products data from reserved quantity log */
            $data = $this->getReservedQtyFromReservedLog($this->order_id, $this->website_id, $this->for);

            /**
             * Change the status to "processed" for the reserved logs of this order.
             * NOTE:
             * This done for single order (order_id) only.
             */
            if (isset($this->order_id)) {
                if ($this->checkIfOrderStillProcessing($this->order_id, $this->website_id, $this->for)) {
                    return;
                }
                $this->handleNewProcessedLogForSpecificOrder($this->order_id, $this->website_id, $this->for);
            }

            foreach ($data as $dodo_product_id => $qty) {
                /* Get main stock for the dodo_product */
                $main_stock = ProductMainStock::find($dodo_product_id);
                if (isset($main_stock)) {
                    /* Actual quantity of the dodo product which are reserved (in reserved log) */
                    $display_reserved_qty = InventoryProductsReservedQuantityLog::whereDodoProductId($dodo_product_id)
                        ->whereStatus("processing")
                        ->sum('quantity');
                    $main_stock->display_reserved_qty = $display_reserved_qty;

                    // if (isset($this->order_id)) {

                    // }
                    // $main_stock->reserved_quantity = $display_reserved_qty;
                    // $main_stock->warehouse_quantity = $main_stock->quantity + $display_reserved_qty;


                    $main_stock->save();
                }
            }
            /* This is for bulk orders only. This won't be executed for single "order_id". */
            $this->initAdjustDisplayReservedQtyTraitForBulk();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * @param integer|string $order_id
     * @param integer $website_id
     * @param string $for
     * @return array $reserved_qty_logs
     */
    private function getInventoryReservedLogs($order_id, $website_id, $platform)
    {
        $reserved_qty_logs = [];
        try {
            if (isset($order_id)) {
                $reserved_qty_logs = InventoryProductsReservedQuantityLog::whereStatus("processing")
                    ->whereOrderId($order_id)
                    ->whereWebsiteId($website_id)
                    ->wherePlatform($platform)
                    ->get();
            } else {
                $reserved_qty_logs = InventoryProductsReservedQuantityLog::whereStatus("processing")
                    ->offset($this->offset)
                    ->limit($this->bulk_limit)
                    ->get();
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return $reserved_qty_logs;
    }


    /**
     * @param integer|string $order_id
     * @param integer $website_id
     * @param string $platform
     * @return array $data_reserved_qty
     */
    private function getReservedQtyFromReservedLog($order_id, $website_id, $platform) {
        $data_reserved_qty = [];
        foreach ($this->getInventoryReservedLogs($order_id, $website_id, $platform) as $log) {
            if (!isset($data[$log->dodo_product_id])) {
                $data_reserved_qty[$log->dodo_product_id] = 0;
            }
            
            $platform = $log->platform;
            if ($this->checkIfOrderStillProcessing($log->order_id, $log->website_id, $platform)) {
                if (!in_array($log->order_id, $this->data_processing_order_ids[$platform])) {
                    array_push($this->data_processing_order_ids[$platform], $log->order_id);
                }
                $data_reserved_qty[$log->dodo_product_id] += $log->quantity; 
            } else {
                $this->handleNewProcessedLogForSpecificOrder($log->order_id, $log->website_id, $platform);
            }
        }
        return $data_reserved_qty;
    }


    /**
     * @param integer|string $order_id
     * @param integer $website_id
     * @param string $platform
     */
    private function handleNewProcessedLogForSpecificOrder($order_id, $website_id, $platform)
    {
        try {
            if (!in_array($order_id, $this->data_processed_order_ids[$platform])) {
                array_push($this->data_processed_order_ids[$platform], $order_id);
            }
            if (isset($this->order_id)) {
                $logs = InventoryProductsReservedQuantityLog::whereOrderId($order_id)
                    ->whereWebsiteId($website_id)
                    ->wherePlatform($platform)
                    ->get();
                foreach ($logs as $log) {
                    $log->status = "processed";
                    $log->save();
                }
            } else {
                HandleNewProcessedLogForSpecificOrderJob::dispatch($order_id, $website_id, $platform)->delay(Carbon::now()->addMilliseconds(200));
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * @param integer|string $order_id
     * @param integer $website_id
     * @param string $platform
     * @param boolean $check_already_handled
     * @return boolean
     */
    private function checkIfOrderStillProcessing($order_id, $website_id, $platform, $check_already_handled=true)
    {
        try {
            if ($check_already_handled) {
                if (in_array($order_id, $this->data_processing_order_ids[$platform])) {
                    return true;
                }
    
                if (in_array($order_id, $this->data_processed_order_ids[$platform])) {
                    return false;
                }
            }

            if ($platform == $this->getTagForShopeePlatform()) {
                $order_purchase = ShopeeOrderPurchase::whereOrderId($order_id)->whereWebsiteId($website_id)->first();
                if (isset($order_purchase, $order_purchase->status_custom) and 
                    $order_purchase->status_custom == strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING)
                ) {
                    return true;
                }
            } else if ($platform == $this->getTagForLazadaPlatform()) {
                $order_purchase = LazadaOrderPurchase::whereOrderId($order_id)->whereWebsiteId($website_id)->first();
                if (isset($order_purchase, $order_purchase->status_custom) and 
                    $order_purchase->status_custom == strtolower(LazadaOrderPurchase::ORDER_STATUS_PROCESSING)
                ) {
                    return true;
                }
            } else if ($platform == $this->getTagForWooCommercePlatform()) {
                $order_purchase = WooOrderPurchase::whereOrderId($order_id)->whereWebsiteId($website_id)->first();
                if (isset($order_purchase, $order_purchase->status) and 
                    $order_purchase->status == WooOrderPurchase::ORDER_STATUS_PROCESSING
                ) {
                    return true;
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


   /**
    * Check if the "display_reserved_qty" should be updated for products in a specific order.
    *
    * @param string $order_id
    * @param string $website_id
    * @param string $for
    * @return boolean
    */
    public function checkIfDisplayReservedQtyShouldBeUpdated($order_id, $website_id, $for="")
    {
        try {
            $obj = null;
            $for = strtolower($for);

            if (in_array($for, [
                $this->getTagForShopeePlatform(),
                $this->getTagForLazadaPlatform(),
                $this->getTagForWooCommercePlatform()
            ])) {
                /** 
                 * Check if the products under the given $order_id has been already handled to update inventory.
                 * If no such entry found then that means no need to update "display_reserved_quantity" because 
                 * "reserved_quantity" and "display_reserved_qty" was not updated for the products.
                 */
                $obj = InventoryProductsStockUpdate::whereOrderId($order_id)
                    ->wherePlatform($this->getPlatformNo($for))
                    ->wherePlatformSid($website_id)
                    ->first();
                if (isset($obj)) {
                    /**
                     * Check if products under the "order" has the status "processing" still.
                     * This means the "display_reserved_quantity" for the products under this order has not been handled yet.
                     */
                    $obj = InventoryProductsReservedQuantityLog::whereOrderId($order_id)
                        ->wherePlatform($for)
                        ->whereWebsiteId($website_id)
                        ->whereStatus("processing")
                        ->first();
                    if (isset($obj)) {
                        /**
                         * Check if the order has status/status_custom "processing".
                         * If yes then do nothing.
                         */
                        if(!$this->checkIfOrderStillProcessing($order_id, $website_id, $for, false)) {
                            return true;
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }
}