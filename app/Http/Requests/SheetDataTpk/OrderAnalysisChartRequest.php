<?php

namespace App\Http\Requests\SheetDataTpk;

use Illuminate\Foundation\Http\FormRequest;

class OrderAnalysisChartRequest extends FormRequest
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
            'date_range' => [
                'required'
            ],
            'interval' => [
                'required'
            ]
        ];
    }
}
