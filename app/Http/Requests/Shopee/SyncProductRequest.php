<?php

namespace App\Http\Requests\Shopee;

use App\Models\Shopee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SyncProductRequest extends FormRequest
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $shopIds = [];

    /**
     * Create new instance
     *
     * @return void
     */
    public function __construct()
    {
        $sellerId = Auth::user()->id;

        $this->shopIds = Shopee::selectRaw('shop_id')->where('seller_id', $sellerId)->get()->map(function ($shop) {
            return $shop->shop_id;
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
            'shop_id' => [
                'required', 'in:' . implode(',', $this->shopIds)
            ],
            'number_of_products' => [
                'required', 'integer', 'min:-1'
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
            'shop_id' => ucwords(__('translation.shop')),
            'number_of_products' => ucwords(__('translation.sync_record_total'))
        ];
    }
}
