<?php

namespace App\Jobs;

use App\Models\LazadaOrderPurchase;
use App\Models\ProductMainStock;
use App\Models\ShopeeOrderPurchase;
use App\Models\WooOrderPurchase;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * This resets the value for "reserved_quantity" and set the "warehouse_quantity" to same as "quantity"(available quantity).
 * Then the orders having "status_custom"(for "Shopee" and "Lazada") value "processing" or "status" (for "Woo Commerce") value "processing"
 * are retrieved from database and checked by the system if this orders have been already handled for saving "reserved_quantity" and adjusting 
 * the "quantity" count. 
 * The orders not handled then products in those orders are retrieved and then the qunatity is counted.
 * This counted qunatity is stored in as "reserved_quantity" and "quantity" is updated in the system and in market places via respective API calls.
 */
class InitialSetupForWarehouseQtyForInventoryProductQtyManagement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $limit;
    private $time_per_order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        $this->limit = 20;
        $this->time_per_order = 2;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->resetQuantityRelatedInfo();
        $this->initialUpdateQuantityForShopee();
        $this->initialUpdateQuantityForLazada();
        $this->initialUpdateQuantityForWooCommerce();
    }


    /**
     * Initial setup for the value of warehouse_quantity.
     */
    private function resetQuantityRelatedInfo()
    {
        $rows = ProductMainStock::get();
        foreach ($rows as $row) {
            $row->warehouse_quantity = $row->quantity;
            $row->available_quantity = 0;
            $row->reserved_quantity = 0;
            $row->save();
        }
    }


    /**
     * Initiate the job for handling shopee orders(processing) in bulk.
     */
    private function initialUpdateQuantityForShopee()
    {
        try {
            $orderPurcahses = ShopeeOrderPurchase::whereStatusCustom(strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING))->get();
            $arr = [];
            $time_delay = 0;
            foreach ($orderPurcahses as $orderPurchase) {
                array_push($arr, $orderPurchase->order_id);
                if (sizeof($arr) == $this->limit) {
                    InitialSetupForWarehouseQtyForInventoryProductQtyManagementForShopee::dispatch($arr)->delay(Carbon::now()->addSeconds($time_delay));
                    $arr = [];
                    $time_delay += $this->limit*$this->time_per_order;
                }
            }
            if (sizeof($arr) > 0) {
                InitialSetupForWarehouseQtyForInventoryProductQtyManagementForShopee::dispatch($arr)->delay(Carbon::now()->addSeconds($time_delay));
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function initialUpdateQuantityForLazada()
    {
        try {
            $orderPurcahses = LazadaOrderPurchase::whereStatusCustom(strtolower(LazadaOrderPurchase::ORDER_STATUS_PROCESSING))->get();
            $arr = [];
            $time_delay = 0;
            foreach ($orderPurcahses as $orderPurchase) {
                array_push($arr, $orderPurchase->order_id);
                if (sizeof($arr) == $this->limit) {
                    InitialSetupForWarehouseQtyForInventoryProductQtyManagementForLazada::dispatch($arr)->delay(Carbon::now()->addSeconds($time_delay));
                    $arr = [];
                    $time_delay += $this->limit*$this->time_per_order;
                }
            }
            if (sizeof($arr) > 0) {
                InitialSetupForWarehouseQtyForInventoryProductQtyManagementForLazada::dispatch($arr)->delay(Carbon::now()->addSeconds($time_delay));
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function initialUpdateQuantityForWooCommerce()
    {
        try {
            $orderPurcahses = WooOrderPurchase::whereStatus(strtolower(WooOrderPurchase::ORDER_STATUS_PROCESSING))->get();
            $arr = [];
            $time_delay = 0;
            foreach ($orderPurcahses as $orderPurchase) {
                array_push($arr, $orderPurchase->order_id);
                if (sizeof($arr) == $this->limit) {
                    InitialSetupForWarehouseQtyForInventoryProductQtyManagementForWooCommerce::dispatch($arr)->delay(Carbon::now()->addSeconds($time_delay));
                    $arr = [];
                    $time_delay += $this->limit*$this->time_per_order;
                }
            }
            if (sizeof($arr) > 0) {
                InitialSetupForWarehouseQtyForInventoryProductQtyManagementForWooCommerce::dispatch($arr)->delay(Carbon::now()->addSeconds($time_delay));
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
