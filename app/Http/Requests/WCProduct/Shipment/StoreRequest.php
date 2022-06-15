<?php

namespace App\Http\Requests\WCProduct\Shipment;

use App\Models\WooOrderPurchase;
use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StoreRequest extends FormRequest
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $allowedOrderIds = [];

    /**
     * Create new instance
     *
     * @return void
     */
    public function __construct()
    {
        $sellerId = Auth::user()->id;
        $orderManagementIds = WooOrderPurchase::selectRaw('order_id')->where('seller_id', $sellerId)->get();
        $this->allowedOrderIds = $orderManagementIds->map(function($order) {
            return $order->order_id;
        })->toArray();
    }

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
            'order_id' => [
                'required',
                'integer',
                'in:' . implode(',', $this->allowedOrderIds)
            ],
            // 'pending_stock' => [
            //     'required',
            //     'in:' . OrderManagement::PENDING_STOCK_NO . ',' . OrderManagement::PENDING_STOCK_YES
            // ],
            'shipment_date' => [
                'nullable',
                'required_if:shipment_status,' . Shipment::SHIPMENT_STATUS_READY_TO_SHIP,
                'date_format:d-m-Y'
            ]
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
            'order_id' => 'Order ID',
            'shipment_date' => 'Shipment Date',
            'pending_stock' => 'Ready to Ship'
        ];
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required_if' => 'The :attribute is required'
        ];
    }
}
