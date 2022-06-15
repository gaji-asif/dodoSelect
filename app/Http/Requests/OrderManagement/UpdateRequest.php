<?php

namespace App\Http\Requests\OrderManagement;

use App\Models\CustomerShippingMethod;
use App\Models\OrderManagement;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'id' => [
                'required', 'integer'
            ],
            'shop_id' => [
                'required_if:customer_type,0', 'integer'
            ],
//            'order_status' => [
//                'required', 'in:' . implode(',', array_keys(OrderManagement::getAllOrderStatus()))
//            ],
            'contact_name' => [
                'required_if:customer_type,0', 'string', 'max:50'
            ],
            'customer_name' => [
                'required', 'string', 'min:3', 'max:50'
            ],
            'contact_phone' => [
                'required', 'string', 'min:10', 'max:20'
            ],
            'channel_id' => [
                'required_if:customer_type,0', 'integer'
            ],
            'shipping_name' => [
                'required', 'string', 'max:100'
            ],
            'shipping_address' => [
                'required'
            ],
            'shipping_phone' => [
                'required', 'digits_between:9,10'
            ],
            'shipping_province' => [
                'required', 'string', 'max:100'
            ],
            'shipping_district' => [
                'required', 'string', 'max:100'
            ],
            'shipping_sub_district' => [
                'required', 'string', 'max:100'
            ],
            'shipping_postcode' => [
                'required', 'string', 'max:100'
            ],

            'product_id' => [
                'required', 'array'
            ],
            'product_id.*' => [
                'required', 'integer'
            ],
            'product_price' => [
                'required', 'array'
            ],
            'product_price.*' => [
                'required', 'numeric', 'min:0'
            ],
            'product_discount' => [
                'required', 'array'
            ],
            'product_discount.*' => [
                'required', 'numeric', 'min:0'
            ],
            'product_weight' => [
                'required', 'array'
            ],
            'product_weight.*' => [
                'required', 'numeric', 'min:0'
            ],
            'product_qty' => [
                'required', 'array'
            ],
            'product_qty.*' => [
                'required', 'numeric', 'min:0'
            ],

            'shipping_method_id' => [
                'required', 'array'
            ],
            'shipping_method_id.*' => [
                'required', 'integer', 'min:0'
            ],
            'shipping_method_name' => [
                'required', 'array'
            ],
            'shipping_method_name.*' => [
                'required', 'string', 'max:100'
            ],
            'shipping_method_price' => [
                'required', 'array'
            ],
            'shipping_method_price.*' => [
                'required', 'numeric', 'min:0'
            ],
            'shipping_method_discount' => [
                'required', 'array'
            ],
            'shipping_method_discount.*' => [
                'required', 'numeric', 'min:0'
            ],
            'shipping_method_selected' => [
                'required', 'array'
            ],
            'shipping_method_selected.*' => [
                'required', 'in:' . implode(',', array_keys(CustomerShippingMethod::getAllIsSelected()))
            ],

            'tax_enable' => [
                'required', 'in:' . implode(',', array_keys(OrderManagement::getAllTaxEnableValues()))
            ],
            'company_name' => [
                'nullable', 'max:50'
            ],
            'tax_number' => [
                'nullable', 'max:20'
            ],
            'company_phone_number' => [
                'nullable', 'max:20'
            ],
            'company_contact_name' => [
                'nullable', 'max:30'
            ],
            'company_address' => [
                'nullable'
            ],
            'company_province' => [
                'nullable', 'max:50'
            ],
            'company_district' => [
                'nullable', 'max:50'
            ],
            'company_sub_district' => [
                'nullable', 'max:50'
            ],
            'company_postcode' => [
                'nullable', 'min:5', 'max:5'
            ],
        ];
    }

    /**
     * The the validation attributes name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'order_id' => 'Order ID',
            'shop_id' => 'Shop',
            'order_status' => 'Order Status',
            'channel_id' => 'Channel Name',
            'contact_name' => 'Channel ID',
            'customer_name' => 'Customer Name',
            'contact_phone' => 'Contact Phone',
            'shipping_name' => 'Shipping Customer Name',
            'shipping_address' => 'Shipping Address',
            'shipping_phone' => 'Shipping Phone Number',
            'shipping_province' => 'Shipping Province',
            'shipping_district' => 'Shipping District',
            'shipping_sub_district' => 'Shipping Sub District',
            'shipping_postcode' => 'Shipping Postal Code',
            'product_id' => 'Products',
            'product_id.*' => 'Products',
            'product_price' => 'Products Price',
            'product_price.*' => 'Products Price',
            'product_discount' => 'Products Discount Price',
            'product_discount.*' => 'Products Discount Price',
            'product_qty' => 'Products Order Qty',
            'product_qty.*' => 'Products Order Qty',
            'product_weight' => 'Products Weight',
            'product_weight.*' => 'Products Weight',
            'shipping_method_id' => 'Shipping Methods',
            'shipping_method_id.*' => 'Shipping Methods',
            'shipping_method_name' => 'Shipping Methods Name',
            'shipping_method_name.*' => 'Shipping Methods Name',
            'shipping_method_price' => 'Shipping Methods Cost',
            'shipping_method_price.*' => 'Shipping Methods Cost',
            'shipping_method_discount' => 'Shipping Methods Discount Cost',
            'shipping_method_discount.*' => 'Shipping Methods Discount Cost',
            'shipping_method_selected' => 'Shipping Methods Public Page',
            'shipping_method_selected.*' => 'Shipping Methods Public Page',
            'tax_enable' => 'Tax',
            'company_name' => 'Tax - Company Name',
            'tax_number' => 'Tax - Tax Number',
            'company_phone_number' => 'Tax - Phone Number',
            'company_contact_name' => 'Tax - Contact Name',
            'company_address' => 'Tax - Company Address',
            'company_province' => 'Tax - Company Province',
            'company_district' => 'Tax - Company District',
            'company_sub_district' => 'Tax - Company Sub-District',
            'company_postcode' => 'Tax - Company Postal Code',
        ];
    }

    /**
     * Get validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required_if' => __('translation.validation.required')
        ];
    }
}
