<?php

namespace App\Http\Requests\WCProduct\Shipment;

use App\Models\OrderManagement;
use App\Models\WooOrderPurchase;
use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateRequest extends FormRequest
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $allowedShipmentIds = [];

    /**
     * Create new instance
     *
     * @return void
     */
    public function __construct()
    {
        $sellerId = Auth::user()->id;

        $shipmentIds = Shipment::selectRaw('id')->where('seller_id', $sellerId)->get();
        $this->allowedShipmentIds = $shipmentIds->map(function($order) {
            return $order->id;
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
            'shipment_id' => [
                'required',
                'integer',
                'in:' . implode(',', $this->allowedShipmentIds)
            ],
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
            'shipment_id' => 'Shipment ID',
            'shipment_date' => 'Shipment Date',
            'ready_to_ship' => 'Ready to Ship'
        ];
    }
}
