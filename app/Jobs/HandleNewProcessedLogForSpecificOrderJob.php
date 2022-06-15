<?php

namespace App\Jobs;

use App\Models\InventoryProductsReservedQuantityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleNewProcessedLogForSpecificOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $order_id, $website_id, $platform;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id, $website_id, $platform)
    {
        $this->order_id = $order_id;
        $this->website_id = $website_id;
        $this->platform = $platform;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $logs = InventoryProductsReservedQuantityLog::whereOrderId($this->order_id)
            ->whereWebsiteId($this->website_id)
            ->wherePlatform($this->platform)
            ->get();
        foreach ($logs as $log) {
            $log->status = "processed";
            $log->save();
        }
    }
}
