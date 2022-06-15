<?php

namespace App\Jobs;

use App\Models\ShopeeProduct;
use App\Traits\ShopeeOrderPurchaseTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ShopeeProductVariationInfoUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait;
    private $shopee_shop_id, $product_id, $variation_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $product_id, $variation_data)
    {
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->product_id = (int) $product_id;
        $this->variation_data = (array) $variation_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $client = $this->getShopeeClient($this->shopee_shop_id);
            if (isset($client)) {
                foreach ($this->variation_data as $data) {
                    if (isset($data["variation_id"])) {
                        $shopee_product = ShopeeProduct::whereParentId($this->product_id)
                            ->whereProductId((int)$data["variation_id"])
                            ->first();
                        if (isset($shopee_product)) {
                            if (isset($data["price"])) {
                                $this->updateVariationPrice($client, (int)$data["variation_id"], (float)$data["price"]);
                                $shopee_product->price = (float)$data["price"];
                            }
                            if (isset($data["stock"])) {
                                $this->updateVariationStock($client, (int)$data["variation_id"], (int)$data["stock"]);
                                $shopee_product->quantity = (int)$data["stock"];
                            }
                            if (isset($data["variation_sku"])) {
                                $this->updateVariationBasicInfo($client, (int)$data["variation_id"], "", $data["variation_sku"]);
                                $shopee_product->product_code = $data["variation_sku"];
                            }
                            $shopee_product->save();
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function updateVariationPrice($client, $variation_id, $price)
    {
        try {
            $client->item->updateVariationPrice([
                'item_id'       => $this->product_id,
                'variation_id'  => $variation_id,
                'price'         => $price,
                'timestamp'     => time()
            ]);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function updateVariationStock($client, $variation_id, $stock)
    {
        try {
            $client->item->updateVariationStock([
                'item_id'       => $this->product_id,
                'variation_id'  => $variation_id,
                'stock'         => $stock,
                'timestamp'     => time()
            ]);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function updateVariationBasicInfo($client, $variation_id, $name, $sku)
    {
        try {
            $client->item->updateItem([
                'item_id'       => $this->product_id,
                'variations'    => [
                    [
                        'variation_id'  => $variation_id,
                        // 'name'          => $name,
                        'variation_sku' => $sku
                    ]
                ],
                'timestamp'     => time()
            ]);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
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
            "Shop:{$this->shopee_shop_id}"
        ];
    }
}
