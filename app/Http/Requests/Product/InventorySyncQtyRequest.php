<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class InventorySyncQtyRequest extends FormRequest
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
            'productSyncId' => [
                'required', 'integer'
            ]
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
            'productSyncId' => __('translation.Product ID')
        ];
    }
}
