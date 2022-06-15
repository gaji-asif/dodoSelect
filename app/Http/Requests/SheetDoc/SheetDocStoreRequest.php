<?php

namespace App\Http\Requests\SheetDoc;

use Illuminate\Foundation\Http\FormRequest;

class SheetDocStoreRequest extends FormRequest
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
            'file_name' => [
                'required', 'string', 'max:50'
            ],
            'spreadsheet_id' => [
                'required', 'string', 'size:44'
            ]
        ];
    }

    /**
     * Get the attributes of validation fields.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'file_name' => 'File Name',
            'spreadsheet_id' => 'Spreadsheet ID'
        ];
    }
}
