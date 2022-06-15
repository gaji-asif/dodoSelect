<?php

namespace App\Http\Requests\Admin\Translation;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
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
                'required', 'array'
            ],
            'ids.*' => [
                'integer'
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
            'ids' => 'Translation ID'
        ];
    }
}
