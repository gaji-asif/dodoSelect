<?php

namespace App\Jobs;

use App\Models\ShopeeOrderPurchase;
use App\Traits\Inventory\ShopeeInventoryProductsStockUpdateTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InitialSetupForWarehouseQtyForInventoryProductQtyManagementForShopee implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeInventoryProductsStockUpdateTrait;

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
        $this->initialUpdateQuantityForShopee();
    }


    private function initialUpdateQuantityForShopee()
    {
        try {
            $orderPurcahses = ShopeeOrderPurchase::whereIn("order_id", $this->order_id_list)
                ->whereStatusCustom(strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING))
                ->get();
            foreach ($orderPurcahses as $orderPurchase) {
                if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($orderPurchase->order_id, $orderPurchase->website_id, $this->getTagForShopeePlatform())) {
                    $this->initInventoryQtyUpdateForShopee($orderPurchase);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
