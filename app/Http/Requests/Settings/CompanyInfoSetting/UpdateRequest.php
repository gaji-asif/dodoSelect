<?php

namespace App\Http\Requests\Settings\CompanyInfoSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'tax_number' => [
                'required', 'string', 'max:20'
            ],
            'company_name' => [
                'required', 'string', 'max:50'
            ],
            'company_logo' => [
                'nullable', 'image', 'max:5120'
            ],
            'company_phone' => [
                'required', 'string', 'max:20'
            ],
            'company_contact_person' => [
                'required', 'string', 'max:30'
            ],
            'company_address' => [
                'required'
            ],
            'company_province' => [
                'required', 'string', 'max:50'
            ],
            'company_district' => [
                'required', 'string', 'max:50'
            ],
            'company_sub_district' => [
                'required', 'string', 'max:50'
            ],
            'company_postcode' => [
                'required', 'string', 'min:5', 'max:5'
            ],
        ];
    }

    /**
     * Get validation attributes names.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'tax_number' => 'Tax Number',
            'company_name' => 'Company Name',
            'company_phone' => 'Company Phone',
            'company_contact_person' => 'Contact Person',
            'company_address' => 'Address',
            'company_province' => 'Province',
            'company_district' => 'District',
            'company_sub_district' => 'Sub-District',
            'company_postcode' => 'Postal Code',
        ];
    }
}
