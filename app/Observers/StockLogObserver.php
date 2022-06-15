<?php

namespace App\Observers;

use App\Jobs\InventoryQtySync;
use App\Models\{ActivityLog, Product, StockLog, ProductMainStock};
use App\Traits\Inventory\AdjustInventoryStockTrait;
use App\Traits\Inventory\HandleReservedQuantityTrait;
use App\Traits\Inventory\JobTypeTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log as FacadesLog;
use Log;

class StockLogObserver
{
    use AdjustInventoryStockTrait, HandleReservedQuantityTrait, JobTypeTrait;

    /**
     * Handle the StockLog "created" event.
     *
     * @param  \App\Models\StockLog  $stockLog
     * @return void
     */
    public function created(StockLog $stockLog)
    {
        $productMainStock = ProductMainStock::where('product_id', $stockLog->product_id)->first();
        $currentQty = $productMainStock->quantity;

        if ($stockLog->is_defect == 0) {
            if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_ADD) {
                $productMainStock->quantity = $currentQty + $stockLog->quantity;
                ActivityLog::updateStockActivityLog('Add product ', $stockLog->id, $stockLog->quantity);

                $productMainStock->warehouse_quantity += $stockLog->quantity;
            }

            if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_REMOVE) {
                $new_quantity = $currentQty - $stockLog->quantity;
                if ($new_quantity < 0) {
                    $new_quantity = 0;
                }
                $productMainStock->quantity = $new_quantity;
                ActivityLog::updateStockActivityLog('Remove product ', $stockLog->id, $stockLog->quantity);
            }

            $productMainStock->save();

            /**
             * BRK-W-1622-S
             */
            // sleep(2);
            if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_REMOVE) {
                if ($productMainStock->reserved_quantity > 0) {
                    $dodoProduct = Product::find($stockLog->product_id);
                    if ($productMainStock->reserved_quantity >= $stockLog->quantity) {
                        $nrq = $productMainStock->reserved_quantity - $stockLog->quantity;
                        $productMainStock->reserved_quantity = $nrq;

                        $new_warehouse_quantity = $productMainStock->warehouse_quantity - $stockLog->quantity;
                        if ($new_warehouse_quantity < 0) {
                            $new_warehouse_quantity = 0;
                        }
                        $productMainStock->warehouse_quantity = $new_warehouse_quantity;
                        $productMainStock->save();

                        if ($productMainStock->warehouse_quantity > $productMainStock->quantity) {
                            $productMainStock->quantity += $stockLog->quantity;
                            $productMainStock->save();
                            
                            InventoryQtySync::dispatch($dodoProduct);

                            if ($dodoProduct->child_products) {
                                $child_sku = explode(",", $dodoProduct->child_products);
                                foreach ($child_sku as $child) {
                                    $dodoChildProduct = Product::query()
                                        ->where('product_code', trim($child))
                                        ->with('getQuantity')
                                        ->firstOrFail();
                                    InventoryQtySync::dispatch($dodoChildProduct, $productMainStock->quantity)->delay(Carbon::now()->addSeconds(1));
                                }
                            }
                        }
                    } else {
                        $productMainStock->reserved_quantity = 0;
                        $productMainStock->warehouse_quantity = $productMainStock->quantity;

                        InventoryQtySync::dispatch($dodoProduct);

                        if ($dodoProduct->child_products) {
                            $child_sku = explode(",", $dodoProduct->child_products);
                            foreach ($child_sku as $child) {
                                $dodoChildProduct = Product::query()
                                    ->where('product_code', trim($child))
                                    ->with('getQuantity')
                                    ->firstOrFail();
                                InventoryQtySync::dispatch($dodoChildProduct, $productMainStock->warehouse_quantity)->delay(Carbon::now()->addSeconds(1));
                            }
                        }
                    }
                } else {
                    $new_warehouse_quantity = $productMainStock->warehouse_quantity - $stockLog->quantity;
                    if ($new_warehouse_quantity < 0) {
                        $new_warehouse_quantity = 0;
                    }
                    $productMainStock->warehouse_quantity = $new_warehouse_quantity;
                }
                $productMainStock->save();
            }
        }
    }

    /**
     * Handle the StockLog "updated" event.
     *
     * @param  \App\Models\StockLog  $stockLog
     * @return void
     */
    public function updated(StockLog $stockLog)
    {
        $originalStockLogQty = $stockLog->getOriginal('quantity');
        $incomingStockLogQty = $stockLog->quantity;
        $LogQtyDiff = $incomingStockLogQty - $originalStockLogQty;

        $productMainStock = ProductMainStock::where('product_id', $stockLog->product_id)->first();

        if ($stockLog->is_defect == 0) {
            if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_ADD) {
                $updatedQty = $productMainStock->quantity - $originalStockLogQty;
                $updatedQty = $updatedQty + $incomingStockLogQty;
                if ($updatedQty < 0) {
                    $updatedQty = 0;
                }
                $productMainStock->quantity = $updatedQty;

                ActivityLog::updateStockActivityLog('Update added product', $stockLog->id, $LogQtyDiff);
            }

            if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_REMOVE) {
                $updatedQty = $productMainStock->quantity + $originalStockLogQty;
                $updatedQty = $updatedQty - $incomingStockLogQty;
                if ($updatedQty < 0) {
                    $updatedQty = 0;
                }
                $productMainStock->quantity = $updatedQty;

                ActivityLog::updateStockActivityLog('Update removed product ', $stockLog->id, $LogQtyDiff);
            }

            $productMainStock->save();
        }
    }

    /**
     * Handle the StockLog "deleted" event.
     *
     * @param  \App\Models\StockLog  $stockLog
     * @return void
     */
    public function deleted(StockLog $stockLog)
    {
        $productMainStock = ProductMainStock::where('product_id', $stockLog->product_id)->first();
        $currentQty = $productMainStock->quantity;

        if ($stockLog->is_defect == 0) {
            if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_ADD) {
                $new_quantity = $currentQty - $stockLog->quantity;
                if ($new_quantity < 0) {
                    $new_quantity = 0;
                }
                $productMainStock->quantity = $new_quantity;
            }

            if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_REMOVE) {
                $productMainStock->quantity = $currentQty + $stockLog->quantity;
            }

            $productMainStock->save();
            ActivityLog::updateStockActivityLog('Delete stock log', $stockLog->id, $stockLog->quantity, $stockLog->product_id);
        }
    }

    /**
     * Handle the StockLog "restored" event.
     *
     * @param  \App\Models\StockLog  $stockLog
     * @return void
     */
    public function restored(StockLog $stockLog)
    {
        $productMainStock = ProductMainStock::where('product_id', $stockLog->product_id)->first();
        $currentQty = $productMainStock->quantity;

        if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_ADD) {
            $productMainStock->quantity = $currentQty + $stockLog->quantity;
        }

        if ($stockLog->check_in_out == StockLog::CHECK_IN_OUT_REMOVE) {
            $new_quantity = $currentQty - $stockLog->quantity;
                if ($new_quantity < 0) {
                    $new_quantity = 0;
                }
            $productMainStock->quantity = $new_quantity;
        }

        $productMainStock->save();
    }

    /**
     * Handle the StockLog "force deleted" event.
     *
     * @param  \App\Models\StockLog  $stockLog
     * @return void
     */
    public function forceDeleted(StockLog $stockLog)
    {
        //
    }
}
