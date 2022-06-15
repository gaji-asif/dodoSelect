<?php

namespace App\Jobs;

use App\Models\WooShop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class InventoryQtySyncWooCommerce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Collection */
    private $wooProducts;

    /** @var int */
    private $quantity;

    /** @var array */
    private $wooShopIds;

    /**
     * Create a new job instance.
     *
     * @param $wooProducts
     * @param $quantity
     */
    public function __construct($wooProducts, $quantity)
    {
        $this->wooProducts = $wooProducts;
        $this->quantity = $quantity;

        $this->wooShopIds = $this->wooProducts->pluck('website_id')->unique()->values()->all();
    }

    /**
     * @var $product string WooCommerce product collection
     * Triggers only when stock is adjusted
     * @return void
     */
    public function handle()
    {
        $wooProducts = $this->wooProducts;
        $quantity = $this->quantity;

        foreach ($wooProducts as $wooProduct):
            if($quantity != $wooProduct->quantity):
                InventoryQtySyncWooSingleProduct::dispatch($wooProduct, $quantity);
            endif;
        endforeach;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return collect($this->wooShopIds)->map(function ($shopId) {
                return "Shop:{$shopId}";
        })->toArray();
    }
}
