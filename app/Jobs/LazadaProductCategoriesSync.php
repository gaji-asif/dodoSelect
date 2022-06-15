<?php

namespace App\Jobs;

use App\Models\Lazada;
use App\Models\LazadaProductCategory;
use App\Traits\LazadaOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaProductCategoriesSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait;

    private $lazada_id;
    private $lang;
    private $process_type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lazada_id=0, $lang="en_US", $process_type="initiate")
    {
        $this->lazada_id = (int) $lazada_id;
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
                $lazada_shops = Lazada::select("id", "shop_name")->get();
                if (sizeof($lazada_shops) == 0) {
                    Log::debug("No lazada shop found");
                }
                foreach ($lazada_shops as $index => $shop) {
                    LazadaProductCategoriesSync::dispatch($shop->id, $this->lang, "process")->delay(Carbon::now()->addSeconds($index*15));
                }
            } else if ($this->process_type == "process") {
                if (!in_array($this->lang, ["en_US"])) {
                    Log::debug("invalid lang");
                    return;
                }
                $lazada_shop = Lazada::find($this->lazada_id);
                if (!isset($lazada_shop)) {
                    Log::debug("No such lazada shop found for ".$this->lazada_id);
                    return;
                }
                $categories_data = $this->getProductCategoriesList();

                /* Remove old data. */
                LazadaProductCategory::whereLazadaId($this->lazada_id)->delete();

                foreach ($categories_data as $category) {
                    $category = (array) $category;
                    /* Enter parent category data. */
                    $parent_id = $category["category_id"];
                    $lazada_product_category = new LazadaProductCategory();
                    $lazada_product_category->lazada_id = $this->lazada_id;
                    $lazada_product_category->category_id = $category["category_id"];
                    $lazada_product_category->parent_id = 0;
                    $lazada_product_category->category_name = $category["name"];
                    $lazada_product_category->var = $category["var"];
                    $lazada_product_category->leaf = $category["leaf"];
                    $lazada_product_category->save();
                    if (isset($category["children"])) {
                        /* Enter first layer category data. */
                        foreach($category["children"] as $children_1) {
                            $children_1 = (array) $children_1;
                            $parent_id_2 = $children_1["category_id"];
                            $lazada_product_category = new LazadaProductCategory();
                            $lazada_product_category->lazada_id = $this->lazada_id;
                            $lazada_product_category->category_id = $children_1["category_id"];
                            $lazada_product_category->parent_id = $parent_id;
                            $lazada_product_category->category_name = $children_1["name"];
                            $lazada_product_category->var = $children_1["var"];
                            $lazada_product_category->leaf = $children_1["leaf"];
                            $lazada_product_category->save();
                            /* Enter second layer category data. */
                            if (isset($category["children"])) {
                                foreach($category["children"] as $children_2) {
                                    $children_2 = (array) $children_2;
                                    $parent_id_3 = $children_2["category_id"];
                                    $lazada_product_category = new LazadaProductCategory();
                                    $lazada_product_category->lazada_id = $this->lazada_id;
                                    $lazada_product_category->category_id = $children_2["category_id"];
                                    $lazada_product_category->parent_id = $parent_id_2;
                                    $lazada_product_category->category_name = $children_2["name"];
                                    $lazada_product_category->var = $children_2["var"];
                                    $lazada_product_category->leaf = $children_2["leaf"];
                                    $lazada_product_category->save();
                                    /* Enter third layer category data. */
                                    if (isset($children_2["children"])) {
                                        foreach($children_2["children"] as $children_3) {
                                            $children_3 = (array) $children_3;
                                            $lazada_product_category = new LazadaProductCategory();
                                            $lazada_product_category->lazada_id = $this->lazada_id;
                                            $lazada_product_category->category_id = $children_3["category_id"];
                                            $lazada_product_category->parent_id = $parent_id_3;
                                            $lazada_product_category->category_name = $children_3["name"];
                                            $lazada_product_category->var = $children_3["var"];
                                            $lazada_product_category->leaf = $children_3["leaf"];
                                            $lazada_product_category->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get product categories list from "Lazada".
     *
     * @return object
     */
    private function getProductCategoriesList()
    {
        try {
            $client = $this->getLazadaClient();
            if (isset($client)) {
                $access_token = $this->getAccessTokenForLazada($this->lazada_id);
                $obj = $this->getRequestObjectToGetCategoryTree();
                if (isset($client, $obj) and !empty($access_token)) {
                    $response = $client->execute($obj, $access_token);
                    if (isset($response) and $this->isJson($response)) {
                        $data = json_decode($response);
                        if (isset($data->data)) {
                            return (array)$data->data;
                        } else {
                            Log::debug("No categories data found in response for Lazada shop(".$this->lazada_id.").");
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Get category tree request object.
     *
     * @param array $params
     */
    private function getRequestObjectToGetCategoryTree($params=[])
    {
        try {
            return new LazopRequest('/category/tree/get', 'GET');
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->lazada_id}"
        ];
    }
}
