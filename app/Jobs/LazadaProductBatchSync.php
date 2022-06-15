<?php

namespace App\Jobs;

use App\Models\LazadaProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LazadaProductBatchSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string */
    private $products;

    /** @var int */
    private $website_id;

    /** @var int */
    private $auth_id;

    /**
     * Create a new job instance.
     * @param  string  $products
     * @param  int  $website_id
     * @param  int  $auth_id
     * @return void
     */
    public function __construct($products, $website_id, $auth_id)
    {
        $this->products = $products;
        $this->website_id = $website_id;
        $this->auth_id = $auth_id;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        $products = json_decode($this->products);
        if (!isset($products->data, $products->data->products)) {
            return;
        }
        $products = $products->data->products;

        foreach ($products as $product):
            foreach($product->skus as $sku):
                $lazadaProduct = LazadaProduct::where('product_id', $sku->SkuId)
                    ->where('website_id', $this->website_id)
                    ->first();
                if ($lazadaProduct == null):
                    $lazadaProduct = new LazadaProduct();
                endif;
                $lazadaProduct->images = count($product->images) ? json_encode($product->images) : json_encode($sku->Images);
                $lazadaProduct->inventory_id = 0;
                $lazadaProduct->product_id = $sku->SkuId;
                $lazadaProduct->parent_id = $product->item_id;
                $lazadaProduct->dodo_product_id = 0;
                $lazadaProduct->type = 'variable';

                $color_family = isset($sku->color_family) ? '-' . $sku->color_family : '';
                $lazadaProduct->product_name = isset($product->attributes->name) ? $product->attributes->name: '' . $color_family;

                $lazadaProduct->category_id = 0;
                $lazadaProduct->website_id = isset($this->website_id) ? $this->website_id : 0;
                $lazadaProduct->product_code = isset($sku->SellerSku) ? $sku->SellerSku : 'ERR';
                $lazadaProduct->meta_data = "";
                $lazadaProduct->seller_id = $this->auth_id;
                $lazadaProduct->from_where = 0;
                $lazadaProduct->quantity = isset($sku->quantity) ? $sku->quantity : 0;
                $lazadaProduct->incoming = '';
                $lazadaProduct->price = isset($sku->price) ? $sku->price : 0;
                $lazadaProduct->regular_price = isset($sku->price) ? $sku->price : 0;
                $lazadaProduct->sale_price = isset($sku->special_price) ? $sku->special_price : 0;
                $lazadaProduct->price_html = '<span class="lazada-price">' . isset($sku->price) ? $sku->price : 0 . '</span>';
                $lazadaProduct->inventory_link = "";
                $lazadaProduct->status = 'publish';
                $lazadaProduct->specifications = isset($product->attributes) ? json_encode($product->attributes) : NULL;
                $lazadaProduct->variations = json_encode($sku);
                $lazadaProduct->lazada_category_id = isset($product->primary_category) ? (int)$product->primary_category : 0;
                $lazadaProduct->save();
            endforeach;
        endforeach;
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
