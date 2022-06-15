<?php

namespace App\Http\Requests\Shop;

use App\Rules\UniqueShopCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopUpdateRequest extends FormRequest
{
    /** @var int */
    protected $shopId;

    /**
     * Create new instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->shopId = $request->id;
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
            'id' => [
                'required'
            ],
            'name' => [
                'required', 'string', 'max:255'
            ],
            'code' => [
                'required', 'string', 'max:10', new UniqueShopCode(Auth::user()->id, $this->shopId)
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
