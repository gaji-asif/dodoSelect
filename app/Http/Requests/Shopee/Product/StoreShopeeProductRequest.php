<?php

namespace App\Http\Requests\Shopee\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreShopeeProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->getCustomRules();
    }


    private function getCustomRules() 
    {
        $rules = [
            'name'  => 'required|unique:shopee_products,product_name|min:10|max:120',
            'description' => 'required|min:30|max:5000',
            'sku' => 'required|unique:shopee_products,product_code',
            'type' => 'required|in:simple,variable',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'shopee_category_id' => 'required|integer|min:0',
            'website_id' => 'required|integer',
            'package_length' => 'required|integer|min:1',
            'package_width' => 'required|integer|min:1',
            'package_height' => 'required|integer|min:1',
            'brand' => 'required',
            'brand_attribute_id' => 'required|integer',
            'logistic_data' => 'required|json'
        ];

        $request = $this->validationData();
        if (isset($request["type"]) and !empty($request["type"]) and $request["type"] == "variable") {
            /* Variation "name" in "tier_2_variations". */
            $rules['variation_option_name'] = 'required|min:3|max:14';
            
            if (isset($request["total_variations"])) {
                $total_variations = $request["total_variations"];
                
                /* Variation Name */
                if (!isset($request["variation_name"])) {
                    $rules['variation_name'] = 'required|json';
                } else {
                    $variation_names = (array)json_decode($request["variation_name"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_names[$i]) || empty($variation_names[$i])) {
                            $rules["variaion_name_$i"] = "required|string|min:3|max:100";
                        }
                    }
                }

                /* Variation SKU */
                if (!isset($request["variation_sku"])) {
                    $rules['variation_sku'] = 'required|json';
                } else {
                    $variation_skus = (array)json_decode($request["variation_sku"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_skus[$i]) || empty($variation_skus[$i])) {
                            $rules["variaion_sku_$i"] = "required|string|min:3|max:100";
                        }
                    }
                }

                /* Variation Price */
                if (!isset($request["variation_price"])) {
                    $rules['variation_price'] = 'required|json';
                } else {
                    $variation_prices = (array)json_decode($request["variation_price"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_prices[$i])) {
                            $rules["variaion_price_$i"] = "required|float|min:0";
                        }
                    }
                }

                /* Variation Stock */
                if (!isset($request["variation_stock"])) {
                    $rules['variation_stock'] = 'required|json';
                } else {
                    $variation_stocks = (array)json_decode($request["variation_stock"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_stocks[$i])) {
                            $rules["variaion_stock_$i"] = "required|float|min:0";
                        }
                    }
                }
    
                /* Variation Image */
                if (isset($request["variation_images_count"]) and $request["variation_images_count"] > 0) {
                    for ($i=0; $i<$request["variation_images_count"]; $i++) {
                        $rules["variation_image_$i"] = 'required|image|mimes:jpeg,png,jpg|max:2048';
                    }
                }
            }
        }

        /* Cover images. */
        if (isset($request["cover_images_count"]) and $request["cover_images_count"] > 0) {
            for ($i=0; $i<$request["cover_images_count"]; $i++) {
                $rules["cover_image_$i"] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            }
            if ($request["cover_images_count"] == 0) {
                $rules["cover_image_min"] = 'required';
            } else if ($request["cover_images_count"] > 9) {
                $rules["cover_image_max"] = 'required';
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'variation_name.required' => 'Variation name is required',
            'variation_sku.required' => 'Variation sku is required',
            'variation_price.required' => 'Variation price is required',
            'variation_stock.required' => 'Variation stock is required',
            'cover_image_max.required' => 'Can upload at most 9 images',
            'cover_image_min.required' => 'At least 1 cover image required'
        ];
    }
}