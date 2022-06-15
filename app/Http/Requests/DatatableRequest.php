<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DatatableRequest extends FormRequest
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
            'start' => [ 'integer', 'min:0' ],
            'length' => [ 'integer', 'min:0', 'max:100' ],
            'order.0.column' => [ 'integer', 'min:0' ],
            'order.0.dir' => [ 'string', 'in:asc,desc' ]
        ];
    }
}
