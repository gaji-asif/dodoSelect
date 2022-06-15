<?php

namespace App\Jobs;

use App\Models\InventoryProductsStockUpdate;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderWiseProductsAvailableQtyUpdateTrackerRemove implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /* Delete old data from database. */
        InventoryProductsStockUpdate::where('created_at', '<=', Carbon::now()->subDays(15)->format('Y-m-d'))->delete();
    }
}
