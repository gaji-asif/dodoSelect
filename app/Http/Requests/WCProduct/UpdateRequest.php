<?php

namespace App\Http\Requests\WCProduct;

use App\Models\WooProduct;
use App\Rules\WCProduct\RequiredIfHasParent;
use App\Rules\WCProduct\RequiredIfVariableType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $wooProductIds = [];
    private $wooProduct;

    /**
     * Create new instance
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $sellerId = Auth::user()->id;

        $this->wooProductIds = WooProduct::selectRaw('id')->where('seller_id', $sellerId)->get()->map( function ($wooProduct) {
            return $wooProduct->id;
        })->toArray();

        $this->wooProduct = WooProduct::query()
                            ->where('seller_id', $sellerId)
                            ->where('id', $request->id)
                            ->with('parent')
                            ->first();
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
                'required', 'in:' . implode(',', $this->wooProductIds)
            ],
            'product_name' => [
                new RequiredIfVariableType($this->wooProduct), 'string', 'max:255'
            ],
            'product_code' => [
                new RequiredIfVariableType($this->wooProduct), 'string', 'max:255'
            ],
            'price' => [
                new RequiredIfHasParent($this->wooProduct), 'numeric', 'min:0'
            ],
            'quantity' => [
                new RequiredIfHasParent($this->wooProduct), 'integer', 'min:0'
            ]
        ];
    }
}
