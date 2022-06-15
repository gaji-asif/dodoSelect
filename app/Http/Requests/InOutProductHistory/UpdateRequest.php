<?php

namespace App\Http\Requests\InOutProductHistory;

use App\Models\StockLog;
use App\Rules\AvailableStockToRemove;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateRequest extends FormRequest
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $stockLogId;
    private $productId;
    private $type;

    /**
     * Create new instance
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->stockLogId = $request->id;
        $this->productId = $request->product_id;
        $this->type = $request->check_in_out;
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
                'required',
                'integer'
            ],
            'product_id' => [
                'required',
                'integer',
            ],
            'datetime' => [
                'required',
                'date_format:Y-m-d H:i'
            ],
            'check_in_out' => [
                'required',
                'in:' . StockLog::CHECK_IN_OUT_ADD . ', ' . StockLog::CHECK_IN_OUT_REMOVE
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                new AvailableStockToRemove($this->stockLogId, $this->productId, $this->type)
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
            'id' => __('translation.ID'),
            'product_id' => __('translation.Product'),
            'datetime' => __('translation.Date Time'),
            'check_in_out' => __('translation.Type'),
            'quantity' => __('translation.Quantity')
        ];
    }
}
