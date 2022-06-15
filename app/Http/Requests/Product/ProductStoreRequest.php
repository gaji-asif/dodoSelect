<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
        return [
            // 'image' => [
            //     'required',
            //     'image',
            //     'mimes:png,jpg,jpeg'
            // ],
            'product_name' => [
                'required',
                'max:255'
            ],
            'product_code' => [
                'required',
                'unique:products,product_code',
                'max:255',
                'alpha_dash'
            ],
            'price' => [
                'required',
                'integer',
                'min:0'
            ],
            'dropship_price' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'weight' => [
                'required'
            ],
            'pack' => [
                'required'
            ],
            // 'shop_id' => [
            //     'required'
            // ],
            // 'shop_price' => [
            //     'required'
            // ],
            // 'cost_pc' => [
            //     'required'
            // ],
            // 'currency' => [
            //     'required'
            // ],
            'alert_stock' => [
                'required'
            ]
        ];
    }

    /**
     * Get the validation fields name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'category_id' => __('translation.Product Category'),
            'image' => __('translation.Image'),
            'product_name' => __('translation.Product Name'),
            'product_code' => __('translation.Product Code'),
            'specifications' => __('translation.Specifications'),
            'price' => __('translation.Price'),
            'dropship_price' => __('translation.Dropship Price'),
            'weight' => __('translation.Weight'),
            'pack' => __('translation.Pieces / Pack'),
            'shop_id' => __('translation.Shop Name'),
            'shop_price' => __('translation.Shop Price'),
            'cost_pc' => __('translation.Cost / Pc'),
            'currency' => __('translation.Currency'),
            'alert_stock' => __('translation.Alert Stock')
        ];
    }
}
