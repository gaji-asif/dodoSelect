<?php

namespace App\Http\Requests\BuyerPage\BankTransferConfirm;

use App\Models\OrderManagement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreRequest extends FormRequest
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
        return $this->orderManagement->order_status == OrderManagement::ORDER_STATUS_PENDING_PAYMENT;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_date' => [
                'required', 'date', 'date_format:d-m-Y'
            ],
            'payment_time' => [
                'required', 'date_format:H:i'
            ],
            'payment_receipt' => [
                'required', 'image', 'max:5120'
            ]
        ];
    }

    /**
     * Get the validation attributes
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'payment_date' => 'Transfer Date',
            'payment_time' => 'Transfer Time',
            'payment_receipt' => 'Transfer Receipt'
        ];
    }
}
