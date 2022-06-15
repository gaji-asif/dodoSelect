<?php

namespace App\Jobs;

use App\Models\Lazada;
use App\Models\LazadaSetting;
use App\Traits\Inventory\MonitorAdjustProductQtyTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Lazada\LazopClient;
use Lazada\LazopRequest;

class InventoryQtySyncLazada implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MonitorAdjustProductQtyTrait;

    /** @var Collection */
    private $lazadaProducts;

    /** @var int */
    private $quantity;

    /** @var array */
    private $lazadaShopIds;

    /**
     * Create a new job instance.
     *
     * @param  Collection  $lazadaProducts
     * @param  int  $quantity
     * @return void
     */
    public function __construct($lazadaProducts, $quantity)
    {
        $this->lazadaProducts = $lazadaProducts;
        $this->quantity = $quantity;

        $this->lazadaShopIds = $this->lazadaProducts->pluck('website_id')->unique()->values()->all();
    }

    /**
     * @return void
     * @throws Exception
     * @var $product string WooCommerce product collection
     * Triggers only when stock is adjusted
     */
    public function handle()
    {
        $lazadaProducts = $this->lazadaProducts;
        $lazadaSetting = LazadaSetting::first();
        $client = new LazopClient(
            $lazadaSetting->regional_host,
            $lazadaSetting->app_id,
            $lazadaSetting->app_secret
        );

        foreach ($lazadaProducts as $product) {
            if(isset($product->website_id)) {
                $shop = Lazada::find($product->website_id);
                $access_token = json_decode($shop->response)->access_token;
                $lazadaRequest = new LazopRequest('/product/price_quantity/update','POST');
                $lazadaRequest->addApiParam('payload','<Request>
                    <Product>
                        <Skus>
                        <Sku>
                            <ItemId>'.$product->parent_id.'</ItemId>
                            <SkuId>'.$product->product_id.'</SkuId>
                            <SellerSku>'.$product->product_code.'</SellerSku>
                            <Quantity>'.$this->quantity.'</Quantity>
                        </Sku>
                        </Skus>
                    </Product>
                </Request>');
                
                $response = $client->execute($lazadaRequest, $access_token);
                if ($this->shouldUpdateProductQty($response, $this->getTagForLazadaPlatform())) {
                    $product->quantity = $this->quantity;
                    $product->update();
                } else {
                    $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForLazadaPlatform());
                }
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return collect($this->lazadaShopIds)->map(function ($shopId) {
                return "Shop:{$shopId}";
        })->toArray();
    }
}
