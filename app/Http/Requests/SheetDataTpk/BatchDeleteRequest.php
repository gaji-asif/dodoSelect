<?php

namespace App\Http\Requests\SheetDataTpk;

use Illuminate\Foundation\Http\FormRequest;

class BatchDeleteRequest extends FormRequest
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
            'ids' => [
                'required', 'array', 'max:100'
            ],
            'ids.*' => [
                'integer'
            ]
        ];
    }

    /**
     * Get the attributes of the validations rules
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'ids' => __('translation.Data ID')
        ];
    }
}
