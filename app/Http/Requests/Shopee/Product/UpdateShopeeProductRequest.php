<?php

namespace App\Http\Requests\Shopee\Product;

use App\Models\ShopeeProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isJson;

class UpdateShopeeProductRequest extends FormRequest
{
    private $custom_error_message = [];

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
        $rules = [];
        $request = $this->validationData();

        /**
         * Here "id" refers to "product_id" in "shopee_products" table.
         */
        if (!isset($request["id"]) || empty($request["id"])) {
            $shopee_product = ShopeeProduct::whereProductId($request["id"])->first();
            $rules['name'] = 'required|unique:shopee_products,product_name,'.$shopee_product->id.'|min:20|max:120';
            $rules['sku'] = 'required|unique:shopee_products,product_code,'.$shopee_product->id;
        }

        array_push($rules, [
            'description'   => 'required|min:30|max:5000',
            'type'          => 'required|in:simple,variable',
            'price'         => 'required|numeric|min:0',
            'quantity'      => 'required|integer|min:0',
            'website_id'    => 'required|integer',
            'shopee_category_id' => 'required|integer|min:0'
        ]);

        if (isset($request["type"]) and !empty($request["type"]) and $request["type"] == "variable") {
            /* Variation "name" in "tier_2_variations". */
            $rules['option_name_1'] = 'required|min:3|max:14';
            $tier_variation_choices_1__option_val = json_decode($request["tier_variation_choices_1__option_val"]);
            foreach ($tier_variation_choices_1__option_val as $i => $option_val) {
                if (empty($option_val)) {
                    $rules["tier_variation_option_1__option_$i"] = "required";
                    $this->setNewCustomErrorMessage("tier_variation_option_1__option_$i".".required", "Option ".($i+1)." is required for \"Option Name 1\"");
                } else if (strlen($option_val) < 3) {
                    $rules["tier_variation_option_1__option_$i"] = "required";
                    $this->setNewCustomErrorMessage("tier_variation_option_1__option_$i".".required", "Option ".($i+1)." for \"Option Name 1\" must have atleast 3 characters");   
                } else if (strlen($option_val) > 20) {
                    $rules["tier_variation_option_1__option_$i"] = "required";
                    $this->setNewCustomErrorMessage("tier_variation_option_1__option_$i".".max", "Option ".($i+1)." for \"Option Name 1\" must have atleast 20 characters");
                }
            }

            if (isset($request["option_name_2"]) || isset($request["tier_variation_choices_2__option_val"])) {
                $rules['option_name_2'] = 'required|min:3|max:14';

                $tier_variation_choices_2__option_val = json_decode($request["tier_variation_choices_2__option_val"]);
                foreach ($tier_variation_choices_2__option_val as $i => $option_val) {
                    if (empty($option_val)) {
                        $rules["tier_variation_option_2__option_$i"] = "required";
                        $this->setNewCustomErrorMessage("tier_variation_option_2__option_$i".".required", "Option ".($i+1)." is required for \"Option Name 2\"");
                    } else if (strlen($option_val) < 3) {
                        $rules["tier_variation_option_2__option_$i"] = "required";
                        $this->setNewCustomErrorMessage("tier_variation_option_2__option_$i".".required", "Option ".($i+1)." for \"Option Name 2\" must have atleast 3 characters");   
                    } else if (strlen($option_val) > 20) {
                        $rules["tier_variation_option_2__option_$i"] = "required";
                        $this->setNewCustomErrorMessage("tier_variation_option_2__option_$i".".max", "Option ".($i+1)." for \"Option Name 2\" must be under 20 characters");
                    }
                }
            }

            /* For old variations */
            if (isset($request["variation_id"]) and !empty($request["variation_id"]) and isJson($request["variation_id"])) {
                $variation_id = json_decode($request["variation_id"]);
                $total_variations = sizeof($variation_id);

                /* Variation SKU */
                if (!isset($request["variation_sku"]) || empty($request["variation_sku"]) || !isJson($request["variation_sku"])) {
                    $rules['variation_sku'] = 'required';
                    $this->setNewCustomErrorMessage("variation_sku".".required", "Invalid form data, please reload the page and try again!");
                } else {
                    $variation_skus = (array) json_decode($request["variation_sku"]);
                    for($i=0; $i<$total_variations; $i++) {
                        if (!isset($variation_skus[$i]) || empty($variation_skus[$i])) {
                            $rules["variaion_sku_$i"] = "required";
                            $this->setNewCustomErrorMessage("variaion_sku_$i".".required", "SKU for variation no# ".($i+1)." is required");
                        } else if (strlen($variation_skus[$i]) < 5) {
                            $rules["variaion_sku_$i"] = "required";
                            $this->setNewCustomErrorMessage("variaion_sku_$i".".required", "SKU for variation no# ".($i+1)." must have atleast 3 characters");
                        } else if (strlen($variation_skus[$i]) > 20) {
                            $rules["variaion_sku_$i"] = "required";
                            $this->setNewCustomErrorMessage("variaion_sku_$i".".required", "SKU for variation no# ".($i+1)." must be under 20 characters");
                        }
                    }
                }

                /* Variation Price */
                if (!isset($request["variation_price"]) || empty($request["variation_price"]) || !isJson($request["variation_price"])) {
                    $rules['variation_price'] = 'required';
                    $this->setNewCustomErrorMessage("variation_sku".".required", "Invalid form data, please reload the page and try again!");
                } else {
                    $variation_prices = (array) json_decode($request["variation_price"]);
                    for($i=0; $i<$total_variations; $i++) {
                        if (!isset($variation_prices[$i])) {
                            $rules["variation_price_$i"] = "required";
                            $this->setNewCustomErrorMessage("variation_price_$i".".required", "Price for variation no# ".($i+1)." is required");
                        } else if ($variation_prices[$i] <= 0) {
                            $rules["variation_price_$i"] = "required";
                            $this->setNewCustomErrorMessage("variation_price_$i".".required", "Price for variation no# ".($i+1)." is not valid");
                        }
                    }
                }

                /* Variation Stock */
                if (!isset($request["variation_stock"]) || empty($request["variation_stock"]) || !isJson($request["variation_stock"])) {
                    $rules['variation_stock'] = 'required';
                    $this->setNewCustomErrorMessage("variation_stock".".required", "Invalid form data, please reload the page and try again!");
                } else {
                    $variation_stocks = (array) json_decode($request["variation_stock"]);
                    for($i=0; $i<$total_variations; $i++) {
                        if (!isset($variation_stocks[$i])) {
                            $rules["variation_stock_$i"] = "required";
                            $this->setNewCustomErrorMessage("variation_stock_$i".".required", "Stock for variation no# ".($i+1)." is required");
                        } else if ($variation_stocks[$i] < 0) {
                            $rules["variation_stock_$i"] = "required";
                            $this->setNewCustomErrorMessage("variation_stock_$i".".required", "Stock for variation no# ".($i+1)." is not valid");
                        }
                    }
                }

                /* Variation old SKU */
                if (!isset($request["variation_sku_old"]) || empty($request["variation_sku_old"]) || !isJson($request["variation_sku_old"])) {
                    $rules['variation_sku_old'] = 'required';
                    $this->setNewCustomErrorMessage("variation_sku_old".".required", "Invalid form data, please reload the page and try again!");
                } else {
                    $variation_skus_old = (array) json_decode($request["variation_sku_old"]);
                    for($i=0; $i<$total_variations; $i++) {
                        if (!isset($variation_skus_old[$i])) {
                            $rules["variaion_sku_old_$i"] = "required";
                            $this->setNewCustomErrorMessage("variaion_sku_old_$i".".required", "Old SKU for variation no# ".($i+1)." is required");
                        }
                    }
                }

                /* Variation Old Price */
                if (!isset($request["variation_price_old"]) || empty($request["variation_price_old"]) || !isJson($request["variation_price_old"])) {
                    $rules['variation_price_old'] = 'required';
                    $this->setNewCustomErrorMessage("variation_price_old".".required", "Invalid form data, please reload the page and try again!");
                } else {
                    $variation_prices_old = (array) json_decode($request["variation_price_old"]);
                    for($i=0; $i<$total_variations; $i++) {
                        if (!isset($variation_prices_old[$i])) {
                            $rules["variation_price_old_$i"] = "required";
                            $this->setNewCustomErrorMessage("variation_price_old_$i".".required", "Old price for variation no# ".($i+1)." is required");
                        } else if ($variation_prices_old[$i] < 0) {
                            $rules["variation_price_old_$i"] = "required";
                            $this->setNewCustomErrorMessage("variation_price_old_$i".".required", "Old price for variation no# ".($i+1)." is not valid");
                        }
                    }
                }

                /* Variation Old Stock */
                if (!isset($request["variation_stock_old"]) || empty($request["variation_stock_old"]) || !isJson($request["variation_stock_old"])) {
                    $rules['variation_stock_old'] = 'required';
                    $this->setNewCustomErrorMessage("variation_stock_old".".required", "Invalid form data, please reload the page and try again!");
                } else {
                    $variation_stocks_old = (array) json_decode($request["variation_stock_old"]);
                    for($i=0; $i<$total_variations; $i++) {
                        if (!isset($variation_stocks_old[$i])) {
                            $rules["variation_stock_old_$i"] = "required";
                            $this->setNewCustomErrorMessage("variation_stock_old_$i".".required", "Old stock for variation no# ".($i+1)." is required");
                        } else if ($variation_stocks_old[$i] < 0) {
                            $rules["variation_stock_old_$i"] = "required";
                            $this->setNewCustomErrorMessage("variation_stock_old_$i".".required", "Old stock for variation no# ".($i+1)." is not valid");
                        }
                    }
                }

                /* New variation option images for tier variation 2 for "Option 1". */
                for($i=0; $i<$total_variations; $i++) {
                    if (isset($request["new_variation_option_image_".$i])) {
                        $rules["new_variation_option_image_$i"] = "required|image|mimes:jpeg,png,jpg|max:2048";
                        $this->setNewCustomErrorMessage("new_variation_option_image_$i".".required", "Variation image no# ".($i+1)." is required");
                        $this->setNewCustomErrorMessage("new_variation_option_image_$i".".image", "Variation image no# ".($i+1)." is invalid");
                        $this->setNewCustomErrorMessage("new_variation_option_image_$i".".mimes", "Variation image no# ".($i+1)." is invalid");
                        $this->setNewCustomErrorMessage("new_variation_option_image_$i".".max", "Variation image no# ".($i+1)." is invalid");
                    }
                }
            } else {
                $rules["variation_id"] = "required";
                $this->setNewCustomErrorMessage("variation_id".".required", "Invalid form data, please reload the page and try again!");
            }
        }

        return $rules;
    }

    public function messages()
    {
        return array_merge($this->getCustomErrorMessage(), [
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
