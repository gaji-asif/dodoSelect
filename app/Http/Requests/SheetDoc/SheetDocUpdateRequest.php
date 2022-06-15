<?php

namespace App\Http\Requests\SheetDoc;

use App\Models\SheetDoc;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SheetDocUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $id = $this->route('id');
        $sheetDoc = SheetDoc::find($id);

        return Auth::check() && $sheetDoc->seller_id === Auth::user()->id;
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
