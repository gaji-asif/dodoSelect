<?php

namespace App\Http\Requests\OrderManagement\Shipment;

use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PackOrderRequest extends FormRequest
{
    /**
     * Define the properties
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

        $shipments = Shipment::where('seller_id', $sellerId)->get();
        $this->allowedShipmentIds = $shipments->map(function($shipment) {
            return $shipment->id;
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
            'id' => [ 'required', 'in:' . implode(',', $this->allowedShipmentIds) ]
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
            'id' => 'Shipment ID'
        ];
    }
}
