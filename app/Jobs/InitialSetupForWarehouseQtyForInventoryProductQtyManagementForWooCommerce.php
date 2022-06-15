<?php

namespace App\Jobs;

use App\Models\WooOrderPurchase;
use App\Traits\Inventory\WooCommerceInventoryProductsStockUpdateTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InitialSetupForWarehouseQtyForInventoryProductQtyManagementForWooCommerce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, WooCommerceInventoryProductsStockUpdateTrait;

    private $order_id_list;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id_list=[]) {
        $this->order_id_list = $order_id_list;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->initialUpdateQuantityForWooCommerce();
    }


    private function initialUpdateQuantityForWooCommerce()
    {
        try {
            $orderPurcahses = WooOrderPurchase::whereIn("order_id", $this->order_id_list)
                ->whereStatus(strtolower(WooOrderPurchase::ORDER_STATUS_PROCESSING))
                ->get();
            foreach ($orderPurcahses as $orderPurchase) {
                if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($orderPurchase->order_id, $orderPurchase->website_id, $this->getTagForWooCommercePlatform())) {
                    $this->initInventoryQtyUpdateForWooCommerce($orderPurchase);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
