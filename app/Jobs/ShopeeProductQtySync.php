<?php

namespace App\Jobs;

use App\Enums\ShopeeProductTypeEnum;
use App\Models\Product;
use App\Models\ShopeeProduct;
use App\Models\ShopeeSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shopee\Client;

class ShopeeProductQtySync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /** @var ShopeeSetting */
    private $shopeeSetting;

    /** @var int */
    private $shopId;

    /** @var ShopeeProduct */
    private $shopeeProduct;

    /** @var Product */
    private $dodoProduct;

    /**
     * Create a new job instance.
     *
     * @param ShopeeSetting  $shopeeSetting
     * @param $shopId
     * @param $shopeeProduct
     * @param Product  $dodoProduct
     */
    public function __construct(
        ShopeeSetting $shopeeSetting,
        $shopId,
        ShopeeProduct $shopeeProduct,
        Product $dodoProduct
    ) {
        $this->shopeeSetting = $shopeeSetting;
        $this->shopId = (int) $shopId;
        $this->shopeeProduct = $shopeeProduct;
        $this->dodoProduct = $dodoProduct;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $shopeeClient = new Client([
                'baseUrl' => $this->shopeeSetting->host,
                'secret' => $this->shopeeSetting->parent_key,
                'partner_id' => (int) $this->shopeeSetting->parent_id,
                'shopid' => $this->shopId,
            ]);

            $dodoProductQty = $this->dodoProduct->getQuantity->quantity ?? 0;

            $response = false;

            if ($this->shopeeProduct->type == ShopeeProductTypeEnum::simple()->value) {
                $response = $shopeeClient->item->updateStock(
                    [
                        'item_id' => (int) $this->shopeeProduct->product_id,
                        'stock' => (int) $this->shopeeProduct->quantity,
                    ]
                );
            } else {
                $response = $shopeeClient->item->updateVariationStock(
                    [
                        'item_id' => (int) $this->shopeeProduct->parent_id,
                        'variation_id' => (int) $this->shopeeProduct->product_id,
                        'stock' => (int) $this->shopeeProduct->quantity,
                    ]
                );
            }

            if ($response) {
                $this->shopeeProduct->quantity = $dodoProductQty;
                $this->shopeeProduct->update();
            }

        } catch (\Exception $th) {
            report($th);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->shopId}"
        ];
    }
}
