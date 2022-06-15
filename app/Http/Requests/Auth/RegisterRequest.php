<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'username' => [
                'required',
                'unique:users,username',
                'string',
                'max:255'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'contactname' => [
                'required',
                'string',
                'max:255'
            ],
            'phone' => [
                'required',
                'unique:users,phone',
                'numeric'
            ],
            'lineid' => [
                'required',
                'string',
                'max:25'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
                'confirmed',
            ]
        ];
    }

    /**
     * Get the validation attributes name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'username' => 'Username',
            'name' => 'Shop Name',
            'contactname' => 'Contact Name',
            'phone' => 'Mobile Number',
            'logo' => 'Logo',
            'email' => 'Email',
            'lineid' => 'Line Id',
            'password' => 'Password',
            'password_confirmation' => 'Confirm Password'
        ];
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'password.regex' => __('Password must mixed with one uppercase (A-Z), one lowercase (a-z), integers (0-9), Non-alphanumeric ($, !, #, or %) and the minimum length of 8 characters.'),
            'password_confirmation.regex' => __('Password must mixed with one uppercase (A-Z), one lowercase (a-z), integers (0-9), Non-alphanumeric ($, !, #, or %) and the minimum length of 8 characters.')
        ];
    }
}
