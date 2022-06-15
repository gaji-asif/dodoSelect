<?php

namespace App\Http\Requests\OrderManagement\BuyerPage;

use App\Models\OrderManagement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class PlaceOrderRequest extends FormRequest
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
            'payment_method' => [
                'required', 'in:' . implode(',', array_keys(OrderManagement::getAllPaymentMethod()))
            ]
        ];
    }
}
