<?php

namespace App\Jobs;

use App\Models\ShopeeProductBoost;
use App\Traits\ShopeeProductBoostTrait;
use App\Traits\ShopeeTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShopeeSetBoostedProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeTrait, ShopeeProductBoostTrait;

    private $shopee_shop_id;
    private $delete_old_queued_item;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $delete_old_queued_item=true)
    {
        $this->shopee_shop_id = $shopee_shop_id;
        $this->delete_old_queued_item = $delete_old_queued_item;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shopee_shop = $this->getShopeeShopBasedOnShopId((int) $this->shopee_shop_id);
        if (!isset($shopee_shop)) {
            return;
        }

        /* Check products are in "boost" in "Shopee" */
        $total_boosting_products_count = sizeof($this->getQueueProductsBoostingNow($this->shopee_shop_id));
        if ($total_boosting_products_count < $this->getBoostedProductLimit()) {
            $queued_products = ShopeeProductBoost::whereStatus('queued')
                ->whereWebsiteId($this->shopee_shop_id)
                ->orderBy('created_at', 'asc')
                ->limit($this->getBoostedProductLimit()-$total_boosting_products_count)
                ->get();

            if (sizeof($queued_products) > 0) {
                $item_ids = [];
                foreach ($queued_products as $product) {
                    array_push($item_ids, $product->item_id);
                }
                $response = $this->setBoostedProductsByShopeeApi($this->shopee_shop_id, $item_ids);

                /* Check for products successfully started boosting */
                if (isset($response["successes"]) and sizeof($response["successes"]) > 0) {
                    foreach ($queued_products as $product) {
                        if (in_array($product->item_id, $response["successes"])) {
                            ShopeeGetBoostedProduct::dispatch($this->shopee_shop_id, false, false);
                            break;
                        }
                    }
                }

                /* Check for products failed boosting */
                if (isset($response["failures"]) and sizeof($response["failures"]) > 0) {
                    foreach ($response["failures"] as $failed_product) {
                        if ($failed_product["error_code"] == "error_banned") {
                            $duplicate_products = ShopeeProductBoost::whereItemId($failed_product["id"])
                                ->whereStatus("queued")
                                ->get();
                            foreach ($duplicate_products as $duplicate_product) {
                                $duplicate_product->delete();
                            }
                        }
                    }
                    /* Againg look for queued products and initiate again. */
                    ShopeeSetBoostedProduct::dispatch($this->shopee_shop_id);
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
        return [
            "Shop:{$this->shopee_shop_id}"
        ];
    }
}
