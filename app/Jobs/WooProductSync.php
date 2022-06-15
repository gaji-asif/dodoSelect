<?php

namespace App\Jobs;

use App\Models\WooProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\WooProductChildSync;
use Illuminate\Support\Facades\Http;

class WooProductSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $per_page;
    protected $page;
    protected $site_url;
    protected $website_id;
    protected $consumer_key;
    protected $consumer_secret;
    protected $auth_id;

    /**
     * Create a new job instance.
     *
     * @param $per_page
     * @param $page
     * @param $site_url
     * @param $website_id
     * @param $consumer_key
     * @param $consumer_secret
     * @param $auth_id
     */

    public function __construct($per_page, $page, $site_url, $website_id, $consumer_key, $consumer_secret, $auth_id)
    {
        $this->per_page = $per_page;
        $this->page = $page;
        $this->site_url = $site_url;
        $this->website_id = $website_id;
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->auth_id = $auth_id;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        $limit = $this->per_page;
        $url_record = "$this->site_url/wp-json/wc/v3/products?consumer_key=$this->consumer_key&consumer_secret=$this->consumer_secret&pagination_type=page&page=$this->page&limit=$limit&per_page=$this->per_page&status=publish";

        $output = Http::get($url_record);
        $auth_id = $this->auth_id;

        if (!empty($output)) {
            $products = json_decode($output, true);
            foreach ($products as $item) {
                $product_id = $item['id'];

                //Update if already exist
                $product = WooProduct::where('product_id', $product_id)
                    ->where('website_id', $this->website_id)->first();
                if($product === null){
                    $product = new WooProduct();
                }

                $product->seller_id = $this->auth_id;
                $product->website_id = $this->website_id;
                $product->product_id = $item['id'];
                $product->type = $item['type'];
                $product->images = json_encode($item['images']);
                $product->product_name = $item['name'];
                $product->product_code = $item['sku'];
                $product->created_at = $item['date_created'];
                $product->updated_at = $item['date_modified'];
                $product->status = $item['status'];
                $product->quantity = $item['stock_quantity'];
                $product->price = $item['price'];
                $product->regular_price = $item['regular_price'];
                $product->sale_price = $item['sale_price'];
                $product->price_html = $item['price_html'];
                $product->weight = $item['weight'];
                $product->description = $item['description'];
                $product->short_description = $item['short_description'];

                if (!empty($item['variations'])) {
                    $product->variations = json_encode($item['variations']);
                    $parent_id = $item['id'];
                    $variations_url = "$this->site_url/wp-json/wc/v3/products/$parent_id/variations?consumer_key=$this->consumer_key&consumer_secret=$this->consumer_secret&pagination_type=page&page=1&limit=100&per_page=100";
                    WooProductChildSync::dispatch($item['name'],
                        $parent_id, $this->website_id, $this->site_url,
                        $this->consumer_key, $this->consumer_secret, $auth_id, $variations_url
                    );
                }

                $product->save();
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
            "Shop:{$this->website_id}"
        ];
    }
}
