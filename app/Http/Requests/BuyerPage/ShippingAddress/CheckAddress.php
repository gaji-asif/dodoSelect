<?php

namespace App\Http\Requests\BuyerPage\ShippingAddress;

use Illuminate\Foundation\Http\FormRequest;

class CheckAddress extends FormRequest
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
            'shipping_name' => [
                'required', 'string', 'max:100'
            ],
            'shipping_address' => [
                'required'
            ],
            'shipping_phone' => [
                'required', 'digits_between:10,20'
            ],
            'shipping_district' => [
                'required', 'string', 'max:100'
            ],
            'shipping_sub_district' => [
                'required', 'string', 'max:100'
            ],
            'shipping_province' => [
                'required', 'string', 'max:100'
            ],
            'shipping_postcode' => [
                'required', 'string', 'max:100'
            ],
        ];
    }

    /**
     * Get the attributes name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'shipping_name' => 'Shipping Customer Name',
            'shipping_address' => 'Shipping Address',
            'shipping_phone' => 'Shipping Phone Number',
            'shipping_district' => 'Shipping District',
            'shipping_sub_district' => 'Shipping Sub District',
            'shipping_province' => 'Shipping Province',
            'shipping_postcode' => 'Shipping Postal Code',
        ];
    }
}
