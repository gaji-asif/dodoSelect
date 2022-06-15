<?php

namespace App\Http\Requests\SheetName;

use App\Models\SheetDoc;
use App\Rules\UniqueSheetName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SheetNameStoreRequest extends FormRequest
{
    /** @var int */
    protected $sheetDocId;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->sheetDocId = $this->route('sheetDoc');
        $sheetDoc = SheetDoc::find($this->sheetDocId);

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
            'sheet_name' => [
                'required', 'max:50', new UniqueSheetName($this->sheetDocId, null)
            ],
            'allow_to_sync' => [
                'required', 'boolean'
            ]
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'sheet_name' => __('translation.Sheet Name'),
            'allow_to_sync' => __('translation.Auto Sync'),
        ];
    }
}
