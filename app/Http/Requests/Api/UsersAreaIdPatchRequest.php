<?php

namespace App\Http\Requests\Api;

use Illuminate\contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UsersAreaIdPatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // user_idがある場合はログイン中の会員のIDと一致するかチェックする
        if (isset($this->user_id)) {
            $loginUser = Auth::user();

            // ログインしていない場合はfalseを返す
            if (empty($loginUser)) {
                return false;
            }

            // 一致しない場合はfalseを返す
            if ((int) $this->user_id !== $loginUser->id) {
                return false;
            }
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
            'user_id' => 'bail|required|integer|min:1',
            'area_id' => 'bail|required|integer|min:1|exists:areas,id',
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
