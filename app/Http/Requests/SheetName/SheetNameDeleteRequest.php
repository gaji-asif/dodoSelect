<?php

namespace App\Http\Requests\SheetName;

use App\Models\SheetName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SheetNameDeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $sheetNameId = $this->route('id');
        $sheetName = SheetName::find($sheetNameId);

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
            //
        ];
    }
}
