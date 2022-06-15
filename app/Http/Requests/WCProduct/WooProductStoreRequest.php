<?php

namespace App\Http\Requests\WCProduct;

use App\Models\WooProduct;
use Illuminate\Foundation\Http\FormRequest;

class WooProductStoreRequest extends FormRequest
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
           
            'quantity' => [
                'required',
                'integer',
                'min:0'
            ],
            'shop_id' => [
                'required'
            ],
            
            'product_type' => [
                'required'
            ],
            'status' => [
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
            'shop_id' => __('translation.Shop Name'),
            'product_name' => __('translation.Product Name'),
            'product_code' => __('translation.Product Code'),
            'price' => __('translation.price'),
            'quantity' => __('translation.quantity')
            
        ];
    }
}
