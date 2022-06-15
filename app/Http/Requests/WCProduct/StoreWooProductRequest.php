<?php

namespace App\Http\Requests\WCProduct;

use Illuminate\Foundation\Http\FormRequest;

class StoreWooProductRequest extends FormRequest
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
            'id'            => 'required|integer',
            'image'         => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'website_id'    => 'required|integer',
            'product_type'  => 'required',
        ];
    }

 }
