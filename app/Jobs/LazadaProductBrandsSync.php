<?php

namespace App\Jobs;

use App\Models\Lazada;
use App\Models\LazadaProductBrand;
use App\Traits\LazadaOrderPurchaseTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaProductBrandsSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait;

    private $lazada_id;
    private $start_index;
    private $process_type;
    private $page_size;
    private $language_code;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lazada_id=0, $start_index=1, $process_type="initiate", $page_size=200, $language_code="en_US")
    {
        $this->lazada_id = $lazada_id;
        $this->start_index = $start_index;
        $this->process_type = $process_type;
        $this->page_size = $page_size;
        $this->language_code = $language_code;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (!in_array($this->language_code, ["en_US"])) {
                Log::debug("invalid lang");
                return;
            }
            $lazada_shops = Lazada::select("id", "shop_name")->get();
            if (sizeof($lazada_shops) == 0) {
                Log::debug("No lazada shop found");
            }
            $modules = [];
            foreach ($lazada_shops as $shop) {
                $this->lazada_id = $shop->id;
                $data = $this->getProductBrandsList();
                if (!isset($data["module"])) {
                    continue;
                }
                $modules = $data["module"];
                break;
            }
            while (sizeof($modules) > 0) {
                if ($this->start_index == 1) {
                    /* Remove old data. */
                    LazadaProductBrand::whereLazadaId($this->lazada_id)->delete();
                }
                if (sizeof($modules) > 0) {
                    foreach ($modules as $module) {
                        $brand = new LazadaProductBrand();
                        $brand->lazada_id = $this->lazada_id;
                        $brand->brand_id = $module->brand_id;
                        $brand->global_identifier = $module->global_identifier;
                        $brand->name_en = $module->name_en;
                        $brand->name = $module->name;
                        $brand->save();
                    }
                }
                if (isset($data["total_page"]) and $data["total_page"] > ($this->start_index+$this->page_size-1)) {
                    $this->start_index = $this->start_index+$this->page_size;
                    $data = $this->getProductBrandsList();
                    if (!isset($data["module"])) {
                        break;
                    }
                    $modules = $data["module"];
                    sleep(5);
                    continue;
                } else {
                    break;
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
    private function getProductBrandsList()
    {
        try {
            $client = $this->getLazadaClient();
            if (isset($client)) {
                $access_token = $this->getAccessTokenForLazada($this->lazada_id);
                $obj = $this->getRequestObjectToGetBrands();
                if (isset($client, $obj) and !empty($access_token)) {
                    $response = $client->execute($obj, $access_token);
                    if (isset($response) and $this->isJson($response)) {
                        $data = json_decode($response);
                        if (isset($data->data)) {
                            return (array)$data->data;
                        } else {
                            Log::debug("No brands data for products found in response for Lazada shop(".$this->lazada_id.").");
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
    private function getRequestObjectToGetBrands()
    {
        try {
            $request = new LazopRequest('/category/brands/query', 'GET');
            $request->addApiParam('startRow', $this->start_index);
            $request->addApiParam('pageSize', $this->page_size);
            $request->addApiParam('languageCode', $this->language_code);
            return $request;
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
