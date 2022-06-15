<?php

namespace App\Jobs;

use App\Traits\Inventory\MonitorAdjustProductQtyTrait;
use App\Traits\ShopeeTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InventoryQtySyncShopee implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MonitorAdjustProductQtyTrait, ShopeeTrait;

    /** @var Collection */
    private $shopeeProducts;

    /** @var int */
    private $quantity;

    /** @var array */
    private $shopeeShopIds;

    /** @var boolean */
    private $process_as_batch;


    /**
     * Create a new job instance.
     * @param  Collection  $shopeeProducts
     * @param  int  $quantity
     * @param  boolean  $process_as_batch
     * @return void
     */
    public function __construct($shopeeProducts, $quantity, $process_as_batch=false)
    {
        $this->shopeeProducts = $shopeeProducts;
        $this->quantity = $quantity;
        $this->process_as_batch = $process_as_batch;

        $this->shopeeShopIds = $this->shopeeProducts->pluck('website_id')->unique()->values()->all();
    }


    /**
     * @var $product string WooCommerce product collection
     * Triggers only when stock is adjusted
     * @return void
     */
    public function handle()
    {
        if ($this->process_as_batch) {
            $this->updateInventoryQtyInBatchInShopee();
        } else {
            foreach ($this->shopeeProducts as $product) {
                $client = $this->getShopeeClient((int) $product->website_id);
                if (isset($client)) {
                    if($product->type == 'simple') {
                        $response = $client->item->updateStock([
                            'item_id'   => (int) $product->product_id,
                            'stock'     => (int) $product->quantity,
                        ]);
                    } else {
                        $response = $client->item->updateVariationStock([
                            'item_id'       => (int) $product->parent_id,
                            'variation_id'  => (int) $product->product_id,
                            'stock'         => (int) $product->quantity,
                        ]);
                    }

                    if ($this->shouldUpdateProductQty($response->getData(), $this->getTagForShopeePlatform())) {
                        $product->quantity = $this->quantity;
                        $product->update();
                    } else {
                        $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForShopeePlatform());
                    }
                } else {
                    /* Save error log */
                    $this->setErrorMessageForMonitoringInventoryProductQtyUpdate("No such client found for shop_id('$product->website_id')");
                    $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForShopeePlatform());
                }
            }
        }
    }


    /**
     * Update product quantity in shopee as batch.
     */
    private function updateInventoryQtyInBatchInShopee()
    {
        try {
            /* "website_id" and product "type" based payload to used as payload for api. */
            $payload = [];
            /* Payload for "simple" product. */
            $simple_product_objs_arr = [];
            /* Payload for "variable" product. */
            $variable_product_objs_arr = [];
            /* Website id array. */
            $website_ids_arr = [];

            /* Prepare payload to be used in api call to update stock in batch. */
            foreach ($this->shopeeProducts as $product) {
                if (!in_array($product->website_id, $website_ids_arr)) {
                    $payload[(int) $product->website_id]["simple"] = [];
                    $payload[(int) $product->website_id]["variable"] = [];
                    array_push($website_ids_arr, (int) $product->website_id);
                }

                if ($product->type == 'simple') {
                    array_push($payload[(int) $product->website_id]["simple"], [
                        'item_id'   => (int) $product->product_id,
                        'stock'     => (int) $product->quantity,
                    ]);

                    /* "product_id" will "item_id" in response */
                    $simple_product_objs_arr[(int) $product->product_id] = $product;
                } else {
                    array_push($payload[(int) $product->website_id]["variable"], [
                        'item_id'       => (int) $product->parent_id,
                        'variation_id'  => (int) $product->product_id,
                        'stock'         => (int) $product->quantity,
                    ]);

                    /* "parent_id" will "item_id" in response */
                    $variable_product_objs_arr[(int) $product->parent_id] = $product;
                }
            }

            if (sizeof($website_ids_arr) > 0) {
                foreach ($website_ids_arr as $website_id) {
                    $client = $this->getShopeeClient((int) $website_id);
                    if (isset($client)) {
                        /* Update stock for "simple" product. */
                        if (sizeof($payload[$website_id]["simple"]) > 0) {
                            $response = $client->item->updateStockBatch([
                                "items" => $payload[$website_id]["simple"]
                            ]);
                            if (isset($response) and !empty($response)) {
                                $response = $response->getData();
                            }

                            if (isset($response["batch_result"])) {
                                /* Update product quantity in data which have been successfully updated in Shopee. */
                                if(isset($response["batch_result"]["modifications"]) and sizeof($response["batch_result"]["modifications"]) > 0) {
                                    foreach ($response["batch_result"]["modifications"] as $modification) {
                                        if (!isset($simple_product_objs_arr[(int) $modification["item_id"]])) {
                                            continue;
                                        }
                                        $product = $simple_product_objs_arr[(int) $modification["item_id"]];
                                        if (isset($product)) {
                                            $product->quantity = $this->quantity;
                                            $product->update();
                                        }
                                    }
                                }

                                /* Store error log for products for which stoch have not been updated(failed to update) in Shopee. */
                                if(isset($response["batch_result"]["failures"]) and sizeof($response["batch_result"]["failures"]) > 0) {
                                    foreach ($response["batch_result"]["failures"] as $failure) {
                                        if (!isset($simple_product_objs_arr[(int) $failure["item_id"]])) {
                                            continue;
                                        }
                                        $product = $simple_product_objs_arr[(int) $failure["item_id"]];
                                        if (isset($product)) {
                                            $msg = (isset($failure["error_description"]) and !empty($failure["error_description"]))?$failure["error_description"]:"Failed to determine failure cause.";
                                            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($msg);
                                            $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForShopeePlatform());
                                        }
                                    }
                                }
                            } else {
                                $this->saveErrorMessageForBatchUpdateFromErrorResponse($response, $simple_product_objs_arr, $payload[$website_id]["simple"], "simple");
                            }
                        }

                        /* Update stock for "variable" product. */
                        if (sizeof($payload[$website_id]["variable"]) > 0) {
                            $response = $client->item->updateVariationStockBatch([
                                "variations" => $payload[$website_id]["variable"]
                            ]);
                            if (isset($response) and !empty($response)) {
                                $response = $response->getData();
                            }
                            
                            if (isset($response["batch_result"])) {
                                /* Update product quantity in data which have been successfully updated in Shopee. */
                                if(isset($response["batch_result"]["modifications"]) and sizeof($response["batch_result"]["modifications"]) > 0) {
                                    foreach ($response["batch_result"]["modifications"] as $modification) {
                                        if (!isset($variable_product_objs_arr[(int) $modification["item_id"]])) {
                                            continue;
                                        }
                                        $product = $variable_product_objs_arr[(int) $modification["item_id"]];
                                        if (isset($product)) {
                                            $product->quantity = $this->quantity;
                                            $product->update();
                                        }
                                    }
                                }

                                /* Store error log for products for which stoch have not been updated(failed to update) in Shopee. */
                                if(isset($response["batch_result"]["failures"]) and sizeof($response["batch_result"]["failures"]) > 0) {
                                    foreach ($response["batch_result"]["failures"] as $failure) {
                                        if (!isset($variable_product_objs_arr[(int) $failure["item_id"]])) {
                                            continue;
                                        }
                                        $product = $variable_product_objs_arr[(int) $failure["item_id"]];
                                        if (isset($product)) {
                                            $msg = (isset($failure["error_description"]) and !empty($failure["error_description"]))?$failure["error_description"]:"Failed to determine failure cause for batch stock update.";
                                            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($msg);
                                            $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForShopeePlatform());
                                        }
                                    }
                                }
                            } else {
                                $this->saveErrorMessageForBatchUpdateFromErrorResponse($response, $variable_product_objs_arr, $payload[$website_id]["variable"], "variable");
                            }
                        }
                    } else {
                        /* Save error log */
                        $this->setErrorMessageForMonitoringInventoryProductQtyUpdate("No such client found for shop_id('$product->website_id')");
                        $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForShopeePlatform());
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Save error log for batch product quantity update.
     *
     * @param array $response
     * @param array $shopee_products
     * @param array $payload
     * @param string $type
     */
    private function saveErrorMessageForBatchUpdateFromErrorResponse($response, $shopee_products, $payloads, $type)
    {
        try {
            /* Get the error message */
            $this->setErrorMessageForLogForInventoryQtyShopee($response);
            $error_message = $this->getErrorMessageForMonitoringInventoryProductQtyUpdate();
            if (empty($error_message)) {
                $error_message = "Failed to determine reason for failure.";
            }
            $this->resetErrorMessageForMonitoringInventoryProductQtyUpdate();

            /* Get the "item_id" from the "payloads" */
            $item_ids_arr = [];
            foreach ($payloads as $payload) {
                array_push($item_ids_arr, $payload["item_id"]);
            }

            if (sizeof($item_ids_arr) > 0) {
                foreach ($shopee_products as $product) {
                    $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($error_message);
                    if ($type == "simple") {
                        /* For "simple" product cross match with "product_id" */
                        if (in_array($product->product_id, $item_ids_arr)) {
                            $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForShopeePlatform());
                            continue;
                        }
                    } else {
                        /* For "simple" product cross match with "parent_id" */
                        if (in_array($product->parent_id, $item_ids_arr)) {
                            $this->createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $this->quantity, $this->getTagForShopeePlatform());
                            continue;
                        }
                    }
                    $this->resetErrorMessageForMonitoringInventoryProductQtyUpdate();
                }
            }
        } catch (\Exception $exception) {
            $this->resetErrorMessageForMonitoringInventoryProductQtyUpdate();
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
        return collect($this->shopeeShopIds)->map(function ($shopId) {
            return "Shop:{$shopId}";
        })->toArray();
    }
}
