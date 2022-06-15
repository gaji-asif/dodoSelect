<?php

namespace App\Jobs;

use App\Models\ShopeeProductBoost;
use App\Traits\ShopeeProductBoostTrait;
use App\Traits\ShopeeTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ShopeeGetBoostedProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeTrait, ShopeeProductBoostTrait;

    private $shopee_shop_id;
    private $delete_old_queued_item;
    private $set_queued_item;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $delete_old_queued_item=true, $set_queued_item=false)
    {
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->delete_old_queued_item = $delete_old_queued_item;
        $this->set_queued_item = $set_queued_item;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shopee_shop = $this->getShopeeShopBasedOnShopId($this->shopee_shop_id);
        if (!isset($shopee_shop, $shopee_shop->shop_id)) {
            return;
        }

        /* Remove products which have "boosting" status but not in $items */
        if ($this->delete_old_queued_item) {
            $this->deleteOldBoostingProducts($this->shopee_shop_id);
        }

        $items = $this->fetchBoostedProductsFromShopeeApi((int) $this->shopee_shop_id);
        if (sizeof($items) > 0) {
            foreach ($items as $item) {
                /* Get the item from "shopee_product_boosts" table. */
                $shopee_boosted_product = ShopeeProductBoost::whereItemId($item["item_id"])
                    ->latest()
                    ->first();
                if (!isset($shopee_boosted_product)) {
                    /* If not found in db then add new entry. */
                    $queued_product = new ShopeeProductBoost();
                    $queued_product->item_id = $item["item_id"];
                    $queued_product->website_id = (int) $this->shopee_shop_id;
                    $queued_product->status = "boosting";
                    $queued_product->cooldown_second = (int) $item["cooldown_second"];
                    $queued_product->boost_expires_at = Carbon::now()->addSeconds((int) $item["cooldown_second"])->format("Y-m-d H:i:s");
                    $queued_product->boosted_from = "api";
                    $queued_product->save();
                } else {
                    if ($shopee_boosted_product->status == "boosting") {
                        /* Check if the product already "boosting". */
                        $shopee_boosted_product->cooldown_second = (int) $item["cooldown_second"];
                        $shopee_boosted_product->save();
                        continue;
                    } else if ($shopee_boosted_product->status == "queued") {
                        /* Check if the product has status "queued" then updated status, boost_expires_at */
                        $shopee_boosted_product->status = "boosting";
                        $shopee_boosted_product->cooldown_second = (int) $item["cooldown_second"];
                        $shopee_boosted_product->boost_expires_at = Carbon::now()->addSeconds((int) $item["cooldown_second"])->format("Y-m-d H:i:s");
                        $shopee_boosted_product->boosted_from = "api";
                        $shopee_boosted_product->save();
                    }
                }
            }
        }
        $this->checkQueueAndSetNewBoostedProduct();
    }


    /**
     * Set the "queued" products as "boosting" is not enough products are "boosting" and waiting in queue.
     */
    private function checkQueueAndSetNewBoostedProduct()
    {
        try {
            if ($this->checkQueueForEmptySlotForBoostedProducts($this->shopee_shop_id)) {
                ShopeeSetBoostedProduct::dispatch($this->shopee_shop_id);
            }
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
