<?php

namespace App\Jobs;

use App\Models\ShopeeProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\ShopeeProductChildSync;
use App\Traits\LineBotTrait;
use App\Traits\ShopeeOrderPurchaseTrait;
use Carbon\Carbon;

class ShopeeProductDetailSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait, LineBotTrait;

    protected $item;
    protected $settings;
    protected $auth_id;

    /**
     * Create a new job instance.
     * @param $item
     * @param $settings
     * @param $auth_id
     */
    public function __construct($item, $settings, $auth_id)
    {
        $this->item = $item;
        $this->settings = $settings;
        $this->auth_id = $auth_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $client = $this->getShopeeClient((int) $this->item['shopid']);
            if (!isset($client)) {
                $this->triggerPushMessage("Failed to start syncing products from \"Shopee\". No client found.");
                return;
            }

            $itemResponse = $client->item->getItemDetail([
                'item_id' => $this->item['item_id']
            ]);

            $details = $itemResponse->getData();
            $item = $details['item'];


            if ($item['status'] == 'NORMAL') {
                $product = ShopeeProduct::where('product_id', $item['item_id'])
                    ->where('website_id', $this->item['shopid'])
                    ->first();
                if($product == null) {
                    $product = new ShopeeProduct();
                    $product->inventory_id = 0;
                }
                $product->images = json_encode($item['images']);
                $product->product_id = (int) $item['item_id'];
                if ($item['has_variation']) {
                    $product->type = 'variable';
                    $product->quantity = 0;
                    $product->price = 0;
                    $product->regular_price = 0;
                    $product->sale_price = 0;
                    $product->price_html = '<span class="shopee-price">0</span>';
                    ShopeeProductChildSync::dispatch($item, $this->auth_id)->delay(Carbon::now()->addSeconds(1));
                } else {
                    $product->type = 'simple';
                    $product->quantity = $item['stock'];
                    $product->price = $item['price'];
                    $product->regular_price = $item['original_price'];
                    $product->sale_price = $item['price'];
                    $product->price_html = '<span class="shopee-price">'.$item["currency"].$item["price"].'</span>';
                }
                $product->product_name = $item['name'];
                $product->website_id = (int) $this->item['shopid'];
                $product->product_code = $item['item_sku'];
                $product->variations = json_encode($item['variations']);
                $product->meta_data = "";
                $product->seller_id = $this->auth_id;
                $product->incoming = "";
                $product->weight = $item['weight'];
                $product->inventory_link = "";
                $product->status = 'publish';
                $product->shopee_category_id = $item["category_id"]??0;
                $product->specifications = $item["description"]??'';
                $product->total_cover_images = sizeof($item['images']);
                $product->save();
            }
        } catch (\Exception $exception) {
            $this->triggerPushMessage("Failed to start syncing products from \"Shopee\"");
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
            "Shop:{$this->item['shopid']}"
        ];
    }
}
