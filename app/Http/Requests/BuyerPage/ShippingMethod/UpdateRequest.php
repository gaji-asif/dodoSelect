<?php

namespace App\Http\Requests\BuyerPage\ShippingMethod;

use App\Models\OrderManagement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateRequest extends FormRequest
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $orderManagement;

    /**
     * Create new instance
     *
     * @param  \Illuminate\Http\Request
     * @return void
     */
    public function __construct(Request $request)
    {
        $orderId = $request->route()->parameter('order_id');
        $this->orderManagement = OrderManagement::where('order_id', $orderId)->first();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->orderManagement->order_status == OrderManagement::ORDER_STATUS_PENDING;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shipping_method_id' => [
                'required', 'array'
            ],
            'shipping_method_id.*' => [
                'required', 'integer', 'min:0'
            ],

            'tax_enable' => [
                'required', 'in:' . implode(',', array_keys(OrderManagement::getAllTaxEnableValues()))
            ],
            'company_name' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES, 'max:50'
            ],
            'tax_number' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES, 'max:20'
            ],
            'company_phone_number' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES, 'max:20'
            ],
            'company_contact_name' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES, 'max:30'
            ],
            'company_address' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES
            ],
            'company_province' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES, 'max:50'
            ],
            'company_district' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES, 'max:50'
            ],
            'company_sub_district' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES, 'max:50'
            ],
            'company_postcode' => [
                'required_if:tax_enable,' . OrderManagement::TAX_ENABLE_YES, 'min:5', 'max:5'
            ],
        ];
    }

    /**
     * Get the validation attributes name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'shipping_method_id' => 'Shipping Methods',
            'shipping_method_id.*' => 'Shipping Methods',
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
