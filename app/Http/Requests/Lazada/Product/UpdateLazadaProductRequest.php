<?php

namespace App\Http\Requests\Lazada\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLazadaProductRequest extends FormRequest
{
    private $custom_error_message;

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
            'website_id' => 'required|integer|min:1',
            'type' => 'required|in:variable',
            'lazada_category_id' => 'required|integer|min:1',
            'name'  => 'required|min:6|max:5000',
            'description' => 'required|min:6|max:25000',
            'sku' => 'nullable|unique:lazada_products,product_code',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'package_length' => 'nullable|digits_between:0,9999999999',
            'package_width' => 'nullable|digits_between:0,9999999999',
            'package_height' => 'nullable|digits_between:0,9999999999',
            'package_weight' => 'nullable|digits_between:0,9999999999',
            'brand' => 'nullable',
            'variation_images_count' => 'required|integer|min:0'
        ];

        $request = $this->validationData();
        if (isset($request["type"]) and !empty($request["type"]) and $request["type"] == "variable") {
            
            if (isset($request["total_variations"])) {
                $total_variations = $request["total_variations"];
                
                /* Variation Name */
                if (!isset($request["variation_name"])) {
                    $rules['variation_name'] = 'required|json';
                } 
                // else {
                //     $variation_names = (array)json_decode($request["variation_name"]);
                //     for($i=0; $i<$total_variations;$i++) {
                //         if (!isset($variation_names[$i]) || empty($variation_names[$i])) {
                //             $rules["variaion_name_$i"] = "required|string|min:3|max:100";
                //         }
                //     }
                // }

                /* Variation SKU */
                if (!isset($request["variation_sku"])) {
                    $rules['variation_sku'] = 'required|json';
                } else {
                    $variation_skus = (array)json_decode($request["variation_sku"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_skus[$i]) || empty($variation_skus[$i])) {
                            $rules["variaion_sku_$i"] = "required|string|min:3|max:200";
                            $this->setNewCustomErrorMessage("variaion_sku_$i".".required", "The sku for product variation ".($i+1)." is required");
                            $this->setNewCustomErrorMessage("variaion_sku_$i".".string", "The sku for product variation ".($i+1)." is not valid");
                            $this->setNewCustomErrorMessage("variaion_sku_$i".".min", "The sku for product variation ".($i+1)." is not valid");
                            $this->setNewCustomErrorMessage("variaion_sku_$i".".max", "The sku for product variation ".($i+1)." is not valid");
                        }
                    }
                }

                /* Variation Price */
                if (!isset($request["variation_price"])) {
                    $rules['variation_price'] = 'required|json';
                } else {
                    $variation_prices = (array)json_decode($request["variation_price"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_prices[$i]) || $variation_prices[$i] <= 0) {
                            $rules["variaion_price_$i"] = "required|digits_between:0,9999999999";
                            $this->setNewCustomErrorMessage("variaion_price_$i".".required", "The price for product variation ".($i+1)." is required");
                            $this->setNewCustomErrorMessage("variaion_price_$i".".digits_between", "The price for product variation ".($i+1)." is not valid");
                        }
                    }
                }

                /* Variation Stock */
                if (!isset($request["variation_stock"])) {
                    $rules['variation_stock'] = 'required|json';
                } else {
                    $variation_stocks = (array)json_decode($request["variation_stock"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_stocks[$i]) || $variation_stocks[$i] < 0) {
                            $rules["variaion_stock_$i"] = "required|digits_between:0,9999999999";
                            $this->setNewCustomErrorMessage("variaion_stock_$i".".required", "The stock for product variation ".($i+1)." is required");
                            $this->setNewCustomErrorMessage("variaion_stock_$i".".digits_between", "The stock for product variation ".($i+1)." is not valid");
                        }
                    }
                }
    
                /* Variation Image */
                if (isset($request["variation_images_count"]) and $request["variation_images_count"] > 0) {
                    for ($i=0; $i<$request["variation_images_count"]; $i++) {
                        if (isset($request["variation_images_count_".$i])) {
                            $limit = (int) $request["variation_images_count_".$i];
                            if ($limit < 1 || $limit > 8) {
                                $limit = 8;
                            }
                            for ($j=0; $j<$limit; $j++) {
                                $rules["variation_image_".$i."_".$j] = 'required|image|mimes:jpeg,png,jpg|max:2048';
                                $this->setNewCustomErrorMessage("variation_image_".$i."_".$j.".required", "The image ".($j+1)." for product variation ".($i+1)." must be an image");
                                $this->setNewCustomErrorMessage("variation_image_".$i."_".$j.".image", "The image ".($j+1)." for product variation ".($i+1)." must be an image");
                                $this->setNewCustomErrorMessage("variation_image_".$i."_".$j.".mimes", "The image ".($j+1)." for product variation ".($i+1)." must be an image(png,jpeg,jpg)");
                                $this->setNewCustomErrorMessage("variation_image_".$i."_".$j.".max", "The image ".($j+1)." for product variation ".($i+1)." must under 2MB");
                            }
                        }
                    }
                }

                /* Variation Package Weight */
                if (isset($request["variation_package_weight"])) {
                    $variation_package_weights = (array)json_decode($request["variation_package_weight"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_package_weights[$i]) || empty($variation_package_weights[$i]) || $variation_package_weights[$i] < 0) {
                            $rules["variaion_package_weight_$i"] = "required|digits_between:0,9999999999";
                            $this->setNewCustomErrorMessage("variaion_package_weight_$i".".required", "The package weight for product variation ".($i+1)." is required");
                            $this->setNewCustomErrorMessage("variaion_package_weight_$i".".digits_between", "The package weight for product variation ".($i+1)." is not valid");
                        }
                    }
                }

                /* Variation Package Height */
                if (isset($request["variation_package_height"])) {
                    $variation_package_heights = (array)json_decode($request["variation_package_height"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_package_heights[$i]) || empty($variation_package_heights[$i]) || $variation_package_heights[$i] < 0) {
                            $rules["variaion_package_height_$i"] = "required|digits_between:0,9999999999";
                            $this->setNewCustomErrorMessage("variaion_package_height_$i".".required", "The package height for product variation ".($i+1)." is required");
                            $this->setNewCustomErrorMessage("variaion_package_height_$i".".digits_between", "The package height for product variation ".($i+1)." is not valid");
                        }
                    }
                }

                /* Variation Package Width */
                if (isset($request["variation_package_width"])) {
                    $variation_package_widths = (array)json_decode($request["variation_package_width"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_package_widths[$i]) || empty($variation_package_widths[$i]) ||$variation_package_widths[$i] < 0) {
                            $rules["variaion_package_width_$i"] = "required|digits_between:0,9999999999";
                            $this->setNewCustomErrorMessage("variaion_package_width_$i".".required", "The package width for product variation ".($i+1)." is required");
                            $this->setNewCustomErrorMessage("variaion_package_width_$i".".digits_between", "The package width for product variation ".($i+1)." is not valid");
                        }
                    }
                }

                /* Variation Package Length */
                if (isset($request["variation_package_length"])) {
                    $variation_package_lengths = (array)json_decode($request["variation_package_length"]);
                    for($i=0; $i<$total_variations;$i++) {
                        if (!isset($variation_package_lengths[$i]) || empty($variation_package_lengths[$i]) ||$variation_package_lengths[$i] < 0) {
                            $rules["variaion_package_length_$i"] = "required|digits_between:0,9999999999";
                            $this->setNewCustomErrorMessage("variaion_package_length_$i".".required", "The package length for product variation ".($i+1)." is required");
                            $this->setNewCustomErrorMessage("variaion_package_length_$i".".digits_between", "The package length for product variation ".($i+1)." is not valid");
                        }
                    }
                }
            }
        }

        /* Cover images. */
        if (isset($request["cover_images_count"]) and $request["cover_images_count"] > 0) {
            for ($i=0; $i<$request["cover_images_count"]; $i++) {
                $rules["cover_image_$i"] = 'required|image|mimes:jpeg,png,jpg|max:2048';
                $this->setNewCustomErrorMessage("cover_image_$i".".required", "The image ".($i+1)." for product must be an image");
                $this->setNewCustomErrorMessage("cover_image_$i".".image", "The image ".($i+1)." for product must be an image");
                $this->setNewCustomErrorMessage("cover_image_$i".".mimes", "The image ".($i+1)." for product must be an image(png,jpeg,jpg)");
                $this->setNewCustomErrorMessage("cover_image_$i".".max", "The image ".($i+1)." for product must under 2MB");
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
        return array_merge($this->getCustomErrorMessage(), [
            'name.required'             => 'Product name is required',
            'name.min'                  => 'Product name must be at least 6 characters.',
            'name.max'                  => 'Product name must can\'t be more then 5000 characters.',
            'description.required'      => 'Product description is required',
            'description.min'           => 'Product description must be at least 6 characters.',
            'description.max'           => 'Product description must can\'t be more then 25000 characters.',
            'variation_name.required'   => 'Variation name is required',
            'variation_sku.required'    => 'Variation sku is required',
            'variation_price.required'  => 'Variation price is required',
            'variation_stock.required'  => 'Variation stock is required',
            'cover_image_max.required'  => 'Can upload at most 9 images',
            'cover_image_min.required'  => 'At least 1 cover image required',
            'website_id.min'            => 'Must select a shop',
            'website_id.integer'        => 'Must select a shop',
            'lazada_category_id.min'    => 'Must select a sub sub category'
        ]);
    }


    private function getCustomErrorMessage()
    {
        return $this->custom_error_message;
    }


    private function setNewCustomErrorMessage($rule_name, $rule_message)
    {
        return $this->custom_error_message[$rule_name] = $rule_message;
    }
}
