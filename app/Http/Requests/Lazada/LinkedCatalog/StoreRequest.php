<?php

namespace App\Http\Requests\Lazada\LinkedCatalog;

use App\Models\LazadaProduct;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
{
    private $lazadaProductIds = [];
    private $productIds = [];

    /**
     * Create new instance
     *
     * @return void
     */
    public function __construct()
    {
        $sellerId = Auth::user()->id;

        $this->lazadaProductIds = LazadaProduct::selectRaw('id')->where('seller_id', $sellerId)->get()->map(function ($lazadaProduct) {
            return $lazadaProduct->id;
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
            'lazada_product_id' => [
                'required', 'in:' . implode(',', $this->lazadaProductIds)
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
            'lazada_product_id' => 'Product ID',
            'product_id' => 'Catalog ID'
        ];
    }
}
