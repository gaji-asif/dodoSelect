<?php

namespace App\Jobs;

use App\Models\LazadaOrderPurchase;
use App\Traits\Inventory\LazadaInventoryProductsStockUpdateTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InitialSetupForWarehouseQtyForInventoryProductQtyManagementForLazada implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaInventoryProductsStockUpdateTrait;
    
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
        $this->initialUpdateQuantityForLazada();
    }


    private function initialUpdateQuantityForLazada()
    {
        try {
            $orderPurcahses = LazadaOrderPurchase::whereIn("order_id", $this->order_id_list)
                ->whereStatusCustom(strtolower(LazadaOrderPurchase::ORDER_STATUS_PROCESSING))
                ->get();
            foreach ($orderPurcahses as $orderPurchase) {
                if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($orderPurchase->order_id, $orderPurchase->website_id, $this->getTagForLazadaPlatform())) {
                    $this->initInventoryQtyUpdateForLazada($orderPurchase);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
