<?php

namespace App\Http\Requests\SheetName;

use App\Models\SheetName;
use App\Rules\UniqueSheetName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SheetNameUpdateRequest extends FormRequest
{
    /** @var int */
    protected $sheetDocId;

    /** @var int */
    protected $sheetNameId;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->sheetDocId = $this->route('sheetDoc');
        $this->sheetNameId = $this->route('id');

        $sheetName = SheetName::find($this->sheetNameId);

        return Auth::check() && $sheetName->seller_id === Auth::user()->id;
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
                'required', 'max:50', new UniqueSheetName($this->sheetDocId, $this->sheetNameId)
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
