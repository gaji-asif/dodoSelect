<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ShopeeProduct;
use App\Traits\LineBotTrait;
use App\Traits\ShopeeOrderPurchaseTrait;
use Illuminate\Support\Facades\Log;

class ShopeeProductChildSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait, LineBotTrait;

    private $child;
    private $auth_id;

    /**
     * Create a new job instance.
     * @param $child
     * @param $auth_id
     */
    public function __construct($child, $auth_id)
    {
        $this->child = $child;
        $this->auth_id = $auth_id;
    }


    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        try {
            $client = $this->getShopeeClient((int) $this->child['shopid']);
            if (!isset($client)) {
                $this->triggerPushMessage("Failed to start syncing products from \"Shopee\". No client found.");
                return;
            }

            $variationResponse = $client->item->getTierVariation([
                'item_id' => $this->child['item_id']
            ]);

            $all2TierVariations = $variationResponse->getData();

            if (isset($all2TierVariations["request_id"], $all2TierVariations['variations'], $all2TierVariations['tier_variation'])) {
                $parent_product = ShopeeProduct::whereProductId($this->child['item_id'])
                    ->where('website_id', $this->child['shopid'])
                    ->first();
                if (isset($parent_product)) {
                    $parent_product->tier_2_variations = json_encode($all2TierVariations['tier_variation']);
                    /**
                     * The following data will be used for sorting products with missing params.
                     * NOTE:
                     * Now only checking for cover and variation images.
                     */
                    $addtional_info = $this->getShopeeProductMissingInfo($all2TierVariations['tier_variation']);

                    if($parent_product->type == "variable") {
                        $parent_product->total_size_wise_variation_images = $addtional_info['total_size_wise_variation_images'];
                        $parent_product->total_size_wise_options = $addtional_info['total_size_wise_options'];
                        $parent_product->total_color_wise_variation_images = $addtional_info['total_color_wise_variation_images'];
                        $parent_product->total_color_wise_options = $addtional_info['total_color_wise_options'];
                    } else {
                        $parent_product->total_size_wise_variation_images = 0;
                        $parent_product->total_size_wise_options = 0;
                        $parent_product->total_color_wise_variation_images = 0;
                        $parent_product->total_color_wise_options = 0;
                    }
                    $parent_product->save();

                    foreach ($this->child['variations'] as $variation) {
                        if ($variation['status'] == 'MODEL_NORMAL') {
                            $tierIndices = '';
                            $optionValue = '::';
                            if (count($all2TierVariations['variations'])) {
                                foreach ($all2TierVariations['variations'] as $sV) {
                                    if($sV['variation_id'] == $variation['variation_id']) {
                                        $tierIndices = $sV['tier_index'];
                                    }
                                }

                                $j = 0; $oV = '';
                                foreach ($all2TierVariations['tier_variation'] as $tV) {
                                    $oV .= $tV['name'].': '.$tV['options'][$tierIndices[$j]].'; ';
                                    $j++;
                                }
                                $optionValue .= $oV;
                            } else {
                                $optionValue = '::'.$variation['name'];
                            }

                            $child = ShopeeProduct::where('product_id', $variation['variation_id'])
                                ->where('website_id', $this->child['shopid'])
                                ->first();
                            if ($child == null) {
                                $child = new ShopeeProduct();
                                $child->inventory_id = 0;
                            }

                            if (isset($all2TierVariations['tier_variation'][0]['images_url'])) {
                                $images =  '["'.$all2TierVariations['tier_variation'][0]['images_url'][$tierIndices[0]].'"]';
                            } else if(isset($all2TierVariations['tier_variation'][1]['images_url'])) {
                                $images =  '["'.$all2TierVariations['tier_variation'][0]['images_url'][$tierIndices[0]].'"]';
                            } else {
                                $images = json_encode([]);
                            }
                            $child->images = $images;
                            $child->parent_id = (int) $this->child['item_id'];
                            $child->product_id = (int) $variation['variation_id'];
                            $child->type = 'V-'.$this->child['item_id'];
                            $child->product_name = $this->child['name'].$optionValue;
                            $child->website_id = $this->child['shopid'];
                            $child->product_code = $variation['variation_sku'];
                            $child->variations = json_encode([]);
                            $child->meta_data = "";
                            $child->seller_id = $this->auth_id;
                            $child->quantity = $variation['stock'];
                            $child->incoming = "";
                            $child->price = $variation['price'];
                            $child->regular_price = $variation['original_price'];
                            $child->sale_price = $variation['price'];
                            $child->price_html = '<span class="shopee-price">'.$this->child["currency"].$variation["price"].'</span>';
                            $child->weight = $this->child['weight'];
                            $child->inventory_link = "";
                            $child->status = 'publish';
                            $child->save();
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->triggerPushMessage("Failed to start syncing products from \"Shopee\"");
        }
    }


    /**
     * Get the missing image related information.
     */
    public function getShopeeProductMissingInfo($tier_2_variations)
    {
        $data = [
            "total_color_wise_variation_images" => 0,
            "total_size_wise_variation_images"  => 0,
            "total_color_wise_options" => 0,
            "total_size_wise_options"  => 0
        ];
        try {
            foreach ($tier_2_variations as $index => $tier_variation) {
                if (isset($tier_variation["images_url"])) {
                    if ($index==0) {
                        $data["total_size_wise_variation_images"] = sizeof($tier_variation["images_url"]);
                    } else if ($index==1) {
                        $data["total_color_wise_variation_images"] = sizeof($tier_variation["images_url"]);
                    }
                }
                if (isset($tier_variation["options"])) {
                    if ($index==0) {
                        $data["total_size_wise_options"] = sizeof($tier_variation["options"]);
                    } else if ($index==1) {
                        $data["total_color_wise_options"] = sizeof($tier_variation["options"]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return $data;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->child['shopid']}"
        ];
    }
}
