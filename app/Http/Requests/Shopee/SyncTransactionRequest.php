<?php

namespace App\Http\Requests\Shopee;

use Illuminate\Foundation\Http\FormRequest;

class SyncTransactionRequest extends FormRequest
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
                'required'
            ],
            'date_range' => [
                'required'
            ]
        ];
    }

    /**
     * Get the attributes name.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'shop_id' => __('translation.Shop'),
            'date_range' => __('translation.Date Range')
        ];
    }
}
