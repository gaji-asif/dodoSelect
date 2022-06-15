<?php

namespace App\Http\Requests\WCProduct\LinkedCatalog;

use App\Models\Product;
use App\Models\WooProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreRequest extends FormRequest
{
    private $wooProductIds = [];
    private $productIds = [];

    /**
     * Create new instance
     *
     * @return void
     */
    public function __construct()
    {
        $sellerId = Auth::user()->id;

        $this->wooProductIds = WooProduct::selectRaw('id')->where('seller_id', $sellerId)->get()->map(function ($wooProduct) {
            return $wooProduct->id;
        })->toArray();

        $this->productIds = Product::selectRaw('id')->where('seller_id', $sellerId)->get()->map(function ($product) {
            return $product->id;
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
            'woo_product_id' => [
                'required', 'in:' . implode(',', $this->wooProductIds)
            ],
            'product_id' => [
                'required', 'in:' . implode(',', $this->productIds)
            ]
        ];
    }

    /**
     * Get attributes name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'woo_product_id' => 'Product ID',
            'product_id' => 'Catalog ID'
        ];
    }
}
