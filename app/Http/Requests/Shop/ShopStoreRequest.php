<?php

namespace App\Http\Requests\Shop;

use App\Rules\UniqueShopCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShopStoreRequest extends FormRequest
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
            'name' => [
                'required', 'string', 'max:255'
            ],
            'code' => [
                'required', 'string', 'max:10', new UniqueShopCode(Auth::user()->id)
            ]
        ];
    }

    /**
     * Get the validation attributes name.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => __('translation.Shop Name'),
            'code' => __('translation.Shop Code')
        ];
    }
}
