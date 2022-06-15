<?php

namespace App\Http\Requests\OrderPurchase;

use App\Models\OrderPurchase;
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
            'supplier_id' => [
                'required'
            ],
            'order_date' => [
                'required',
                'date_format:d-m-Y'
            ],
           // 'ship_date' => [
                //'nullable',
                //'date_format:d-m-Y'
            //],
            //'e_a_d_f' => [
                //'nullable',
                //'date_format:d-m-Y'
           // ],
            //'e_a_d_t' => [
                //'nullable',
                //'date_format:d-m-Y'
            //],
            'check' => [
               // 'required',
               // 'in:' . OrderPurchase::SUPPLY_FROM_IMPORT . ',' . OrderPurchase::SUPPLY_FROM_DOMESTIC
            ],
            'factory_tracking' => [
               // 'required_if:check,' . OrderPurchase::SUPPLY_FROM_IMPORT
            ],
            'cargo_ref' => [
               // 'required_if:check,' . OrderPurchase::SUPPLY_FROM_IMPORT
            ],
            'number_of_cartons' => [
              //  'required_if:check,' . OrderPurchase::SUPPLY_FROM_IMPORT
            ],
            'domestic_logistics' => [
                //'required_if:check,' . OrderPurchase::SUPPLY_FROM_IMPORT
            ],
            'number_of_cartons1' => [
              //  'required_if:check,' . OrderPurchase::SUPPLY_FROM_DOMESTIC
            ],
            'domestic_logistics1' => [
               // 'required_if:check,' . OrderPurchase::SUPPLY_FROM_DOMESTIC
            ],
          //  'product_id' => [
               // 'required',
               // 'array'
           // ],
            //'product_id.*' => [
           //     'required',
           //     'integer',
            //    'min:1'
           // ],
           /* 'product_price' => [
                'required',
                'array'
            ],
            */
            /*'product_price.*' => [
                'required',
                'numeric',
                'min:0'
            ],
            */
            // 'exchange_rate_id' => [
            //     'nullable',
            //     'array'
            // ],
           /* 'exchange_rate_id.*' => [
                'required',
                'integer',
                'min:1'
            ],
            'product_quantity' => [
                'required',
                'array'
            ],
            'product_quantity.*' => [
                'required',
                'integer',
                'min:1'
            ]
            */
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
            'supplier_id' => __('translation.Supplier Name'),
            'order_date' => __('translation.Order Date'),
            //'ship_date' => __('translation.Ship Date'),
            //'e_a_d_f' => __('translation.Estimated Arrival Date From'),
            //'e_a_d_t' => __('translation.Estimated Arrival Date To'),
            //'check' => __('translation.Supply From'),
            //'factory_tracking' => __('translation.Factory Tracking'),
            //'cargo_ref' => __('translation.Cargo Reference'),
            'number_of_cartons' => __('translation.Number of Cartons'),
            //'domestic_logistics' => __('translation.Domestic Logistics'),
            //'number_of_cartons1' => __('translation.Number of Cartons'),
            //'domestic_logistics1' => __('translation.Domestic Logistics'),
            'product_id.*' => 'Products',
            //'product_price.*' => 'Product Cost Per Pack',
            //'exchange_rate_id.*' => 'Product Currency',
           // 'product_quantity.*' => 'Product Order Qty',
        ];
    }
}

