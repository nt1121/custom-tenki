<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'bail|required|email:rfc|max:150',
            'password' => 'bail|required|min:8|max:255|regex:/\A[a-zA-Z0-9]+\z/',
            'remember' => 'bail|nullable|boolean',
        ];
    }
}
