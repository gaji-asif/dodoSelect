<?php

namespace App\Http\Requests\BuyerPage\ShippingAddress;

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
            'shipping_province' => 'Shipping Province',
            'shipping_district' => 'Shipping District',
            'shipping_sub_district' => 'Shipping Sub District',
            'shipping_postcode' => 'Shipping Postal Code',
        ];
    }
}
