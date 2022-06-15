<?php

namespace App\Http\Requests\Shopee\LinkedCatalog;

use App\Models\Product;
use App\Models\ShopeeProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
{
    private $shopeeProductIds = [];
    private $productIds = [];

    /**
     * Create new instance
     *
     * @return void
     */
    public function __construct()
    {
        $sellerId = Auth::user()->id;

        $this->shopeeProductIds = ShopeeProduct::selectRaw('id')->where('seller_id', $sellerId)->get()->map(function ($shopeeProduct) {
            return $shopeeProduct->id;
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
            'shopee_product_id' => [
                'required', 'in:' . implode(',', $this->shopeeProductIds)
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
            'shopee_product_id' => 'Product ID',
            'product_id' => 'Catalog ID'
        ];
    }
}
