<?php

namespace App\Jobs;

use App\Models\LazadaProduct;
use App\Traits\LazadaOrderPurchaseTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lazada\LazopRequest;
use Log;

class LazadaProductSyncSpecificItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait;

    private $product_item_id;
    private $website_id;
    private $auth_id;

    /**
     * Create a new job instance.
     * @param $products
     * @param $website_id
     * @param $auth_id
     */
    public function __construct($product_item_id, $website_id, $auth_id)
    {
        $this->product_item_id = (int) $product_item_id;
        $this->website_id = (int) $website_id;
        $this->auth_id = (int) $auth_id;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        $product = $this->getSpecificItem();
        if (!isset($product)) {
            return;
        }

        foreach($product->skus as $index => $sku) {
            $lazadaProduct = LazadaProduct::where('product_id', $sku->SkuId)->first();
            if($lazadaProduct == null) {
                $lazadaProduct = new LazadaProduct();
            }
            $lazadaProduct->images = count($product->images) ? json_encode($product->images) : json_encode($sku->Images);
            $lazadaProduct->inventory_id = 0;
            $lazadaProduct->product_id = $sku->SkuId;
            $lazadaProduct->parent_id = $product->item_id;
            $lazadaProduct->dodo_product_id = 0;
            $lazadaProduct->type = 'variable';

            $color_family = isset($sku->color_family) ? '-'.$sku->color_family : '';
            $lazadaProduct->product_name = $product->attributes->name.$color_family;

            $lazadaProduct->category_id = 0;
            $lazadaProduct->website_id = $this->website_id;
            $lazadaProduct->product_code = $sku->SellerSku;
            $lazadaProduct->meta_data = "";
            $lazadaProduct->seller_id = $this->auth_id;
            $lazadaProduct->from_where = 0;
            $lazadaProduct->quantity = $sku->quantity;
            $lazadaProduct->incoming = '';
            $lazadaProduct->price = $sku->price;
            $lazadaProduct->regular_price = $sku->price;
            $lazadaProduct->sale_price = $sku->special_price;
            $lazadaProduct->price_html = '<span class="lazada-price">'.$sku->price.'</span>';
            $lazadaProduct->inventory_link = "";
            $lazadaProduct->status = (isset($product->Status) and !empty($product->Status))?$product->Status:'publish';
            $lazadaProduct->specifications = isset($product->attributes)?json_encode($product->attributes):NULL;
            $lazadaProduct->variations = json_encode($sku);
            $lazadaProduct->lazada_category_id = isset($product->primary_category)?(int)$product->primary_category:0;
            $lazadaProduct->save();
        }
    }


    private function getSpecificItem()
    {
        try {
            $client = $this->getLazadaClient();
            $obj = $this->getRequestObjectToGetShipmentProviders();
            $access_token = $this->getAccessTokenForLazada($this->website_id);
            if (isset($client, $obj) and !empty($access_token)) {
                $response = $client->execute($obj, $access_token);
                if (isset($response) and $this->isJson($response)) {
                    $data = json_decode($response);
                    if (isset($data->data)) {
                        return $data->data;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Get the request object for lazada to fetch the shipment providers.
     */
    private function getRequestObjectToGetShipmentProviders($params=[])
    {
        try {
            $request = new LazopRequest('/product/item/get', 'GET');
            $request->addApiParam('item_id', $this->product_item_id);
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
            "Shop:{$this->website_id}"
        ];
    }
}
