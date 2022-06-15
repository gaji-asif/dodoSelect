<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\ShopeeProductCategory;
use App\Traits\ShopeeOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ShopeeProductCategoriesSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait;
    private $shopee_shop_id;
    private $process_type;
    private $lang;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id="", $lang="en", $process_type="initiate")
    {
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->lang = $lang;
        $this->process_type = $process_type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->process_type == "initiate") {
                $shopee_shops = Shopee::select("id", "shop_id")->get();
                foreach ($shopee_shops as $index => $shop) {
                    ShopeeProductCategoriesSync::dispatch($shop->shop_id, $this->lang, "process")->delay(Carbon::now()->addSeconds($index*15));
                }
            } else if ($this->process_type == "process") {
                if (!in_array($this->lang, ["en"])) {
                    return;
                }
                $shopee_shop = Shopee::whereShopId($this->shopee_shop_id)->first();
                if (!isset($shopee_shop)) {
                    return;
                }
                $response = $this->getProductCategoriesList();
                if (isset($response['request_id'], $response['categories'])) {
                    /* Remove old data. */
                    ShopeeProductCategory::whereShopeeId($shopee_shop->id)->delete();
                    foreach ($response['categories'] as $category) {
                        /* Enter new data. */
                        $shopee_product_category = new ShopeeProductCategory();
                        $shopee_product_category->shopee_id = $shopee_shop->id;
                        $shopee_product_category->category_id = $category["category_id"];
                        $shopee_product_category->parent_id = $category["parent_id"];
                        $shopee_product_category->category_name = $category["category_name"];
                        $shopee_product_category->has_children = $category["has_children"];
                        $shopee_product_category->max_limit = $category["days_to_ship_limits"]["max_limit"] ?? 0;
                        $shopee_product_category->min_limit = $category["days_to_ship_limits"]["min_limit"] ?? 0;
                        $shopee_product_category->save();
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get product categories list from "Shopee".
     *
     * @return object
     */
    private function getProductCategoriesList() {
        try {
            $client = $this->getShopeeClient($this->shopee_shop_id);
            if (isset($client)) {
                return $client->item->getCategories([
                    'shopid'    => $this->shopee_shop_id,
                    'language'  => $this->lang
                ])->getData();
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
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
