<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UsersPasswordPatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        $loginUser = Auth::user();

        // ログインしていない場合はfalseを返す
        if (empty($loginUser)) {
            return false;
        }

        // テストユーザーの場合はfalseを返す
        if ($loginUser->is_test_user) {
            return false;
        }

        // user_idがある場合はログイン中の会員のIDと一致するかチェックする
        if (isset($this->user_id) && (int) $this->user_id !== $loginUser->id) {
            return false;
        }

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
            'user_id' => ['bail', 'required', 'integer', 'min:1'],
            'password' => 'bail|required|min:8|max:255|regex:/\A[a-zA-Z0-9]+\z/',
            'new_password' => 'bail|required|min:8|max:255|regex:/\A[a-zA-Z0-9]+\z/',
        ];
    }

    /**
     * エラー時にJSONのレスポンスを返す
     * 
     * @param  Illuminate\contracts\Validation\Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        $res = response()->json([
            'status' => 400,
            'errors' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }
}
