<?php

namespace App\Jobs;

use App\Models\WooProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class WooProductVariationsUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $product;
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Product $product
     */
    public function __construct($product, $request)
    {
        $this->product = $product;
        $this->request = $request;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        //
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return void
     */
    public function tags()
    {
        //
    }
}
