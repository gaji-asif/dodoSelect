<?php

namespace App\Http\Requests\Shopee;

use Illuminate\Foundation\Http\FormRequest;

class ShopeeOrderSummaryRequest extends FormRequest
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
            'shop_id' => [
                'nullable'
            ],
            'date_from' => [
                'nullable', 'date_format:Y-m-d'
            ],
            'date_to' => [
                'nullable', 'date_format:Y-m-d'
            ],
            'status' => [
                'nullable'
            ]
        ];
    }
}
