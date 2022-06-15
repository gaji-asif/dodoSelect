<?php

namespace App\Jobs;

use App\Traits\Inventory\AdjustDisplayReservedQtyTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Adjust "display_reserved_qty" for "product_main_stocks".
 */
class AdjustDisplayReservedQty implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AdjustDisplayReservedQtyTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id=null, $website_id=null, $for="", $offset=0)
    {
        $this->setVariablesForAdjustDisplayReservedQtyTrait($order_id, $website_id, $for, $offset);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->initAdjustDisplayReservedQtyTrait();
    }
}
