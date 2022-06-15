<?php

namespace App\Http\Requests\InOut;

use App\Models\StockLog;
use App\Rules\AvailableStockToRemoveMass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreRequest extends FormRequest
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $type;
    private $productIds;
    private $adjustQtys;

    /**
     * Create new instance
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->type = $request->check ?? -1;
        $this->productIds = $request->product_id;
        $this->adjustQtys = $request->adjust_qty;
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
            'check' => [
                'required',
                'in:' . StockLog::CHECK_IN_OUT_ADD . ',' . StockLog::CHECK_IN_OUT_REMOVE
            ],
            'product_id' => [
                'required',
                'array'
            ],
            'product_id.*' => [
                'required',
                'integer',
                'min:1'
            ],
            'adjust_stock' => [
                'required',
                'array',
                new AvailableStockToRemoveMass($this->type, $this->productIds)
            ],
            'adjust_stock.*' => [
                'required',
                'integer',
                'min:1'
            ]
        ];
    }

    /**
     * Get the validation label / attributes name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'check' => __('translation.Type'),
            'product_id' => __('translation.Product'),
            'product_id.*' => __('translation.All of Product'),
            'adjust_stock' => __('translation.Adjust Stock Qty'),
            'adjust_stock.*' => __('translation.All of Adjust Stock Qty'),
        ];
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'check.required' => __('translation.validation.required') . ' Please select `' . __('translation.Add Stock') . '` or `' . __('translation.Remove Stock') . '`',
            'check.in' => __('translation.validation.invalid'),
            'product_id.array' => __('translation.validation.invalid'),
            'adjust_stock.array' => __('translation.validation.invalid')
        ];
    }
}
