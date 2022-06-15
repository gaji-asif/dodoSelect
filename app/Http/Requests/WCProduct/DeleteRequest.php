<?php

namespace App\Http\Requests\WCProduct;

use App\Models\WooProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeleteRequest extends FormRequest
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $wooProductIds = [];

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
            ]
        ];
    }
}
