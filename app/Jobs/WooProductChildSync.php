<?php

namespace App\Jobs;

use App\Models\WooProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Config\Definition\Exception\Exception;
use Illuminate\Support\Facades\Http;

class WooProductChildSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $item_name;
    protected $parent_id;
    protected $website_id;
    protected $site_url;
    protected $consumer_key;
    protected $consumer_secret;
    protected $auth_id;
    protected $variation_url;

    /**
     * Create a new job instance.
     * @param $item_name
     * @param $parent_id
     * @param $website_id
     * @param $site_url
     * @param $consumer_key
     * @param $consumer_secret
     * @param $auth_id
     * @param $variation_url
     */
    public function __construct($item_name, $parent_id, $website_id, $site_url, $consumer_key, $consumer_secret, $auth_id, $variation_url)
    {
        $this->item_name = $item_name;
        $this->parent_id = $parent_id;
        $this->website_id = $website_id;
        $this->site_url = $site_url;
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->auth_id = $auth_id;
        $this->variation_url = $variation_url;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $variations = Http::get($this->variation_url);
        $variations = json_decode($variations);
        foreach ($variations as $variation) {
            if($variation->status == "publish"):
                //Update if already exists
                $child_product = WooProduct::where('product_id', $variation->id)
                    ->where('website_id', $this->website_id)->first();
                if($child_product === null){
                    $child_product = new WooProduct();
                }
                $child_product->seller_id = $this->auth_id;
                $child_product->parent_id = $this->parent_id;
                $child_product->website_id = $this->website_id;
                $child_product->product_id = $variation->id;
                $child_product->type = "V-" . $this->parent_id;
                $child_images[] = $variation->image;
                $child_product->images = json_encode($child_images);
                $child_product->product_name = $this->item_name;
                $child_product->product_code = $variation->sku;
                $child_product->created_at = $variation->date_created;
                $child_product->updated_at = $variation->date_modified;
                $child_product->status = $variation->status;
                $child_product->quantity = $variation->stock_quantity;
                $child_product->price = $variation->price;
                $child_product->weight = $variation->weight;
                $child_product->description = $variation->description;
                $child_product->short_description = isset($variation->short_description) ? $variation->short_description : "";
                if(!empty($variation->attributes)){
                    $child_product->attributes = json_encode($variation->attributes);
                }
                $child_product->save();
            endif;
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
            "Shop:{$this->website_id}"
        ];
    }
}
