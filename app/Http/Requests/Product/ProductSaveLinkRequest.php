<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductSaveLinkRequest extends FormRequest
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
            'salesChannel' => [
                'nullable'
            ],
            'productSyncId' => [
                'required', 'integer'
            ],
            'sync_product_id' => [
                'required', 'integer'
            ],
            'action' => [
                'required', 'in:attach,detach'
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
            'salesChannel' => __('translation.Sales Channel'),
            'productSyncId' => __('translation.Product ID'),
            'sync_product_id' => __('translation.Product Channel ID'),
            'action' => __('translation.Action')
        ];
    }
}
