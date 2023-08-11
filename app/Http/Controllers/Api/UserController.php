<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UsersAreaIdPatchRequest;
use App\Http\Requests\Api\UsersEmailPostRequest;
use App\Http\Requests\Api\UsersPasswordPatchRequest;
use App\Services\EmailChangeService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected EmailChangeService $emailChangeService
    ) {
    }

    /**
     * 会員の地域IDを更新する
     * 
     * @param 　App\Http\Requests\Api\UsersAreaIdPatchRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function updateAreaId(UsersAreaIdPatchRequest $request): JsonResponse
    {
        $user = $this->userService->updateAreaId(Auth::user(), $request->area_id);
        return response()->json([
            'user' => $this->userService->getUserStoreState($user),
        ]);
    }

    /**
     * メールアドレスの変更申請を登録する
     * 
     * @param  App\Http\Requests\Api\UsersEmailPostRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function requestEmailChange(UsersEmailPostRequest $request): JsonResponse
    {
        if (!$this->emailChangeService->createRequest($request->user_id, $request->email)) {
            return response()->json([
                'status' => 404,
                'errors' => ['メールアドレス変更の申請が登録できませんでした。'],
            ], 404);
        }

        return response()->json(['status' => 200]);
    }

    /**
     * 会員のパスワードを更新する
     * 
     * @param  App\Http\Requests\Api\UsersPasswordPatchRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function updatePassword(UsersPasswordPatchRequest $request): JsonResponse
    {
        $loginUser = Auth::user();

        if (!Hash::check($request->password, $loginUser->password)) {
            return response()->json([
                'status' => 400,
                'errors' => ['password' => ['パスワードが間違っています。']],
            ], 400);
        }

        $this->userService->updatePassword($loginUser, $request->new_password);
        return response()->json(['status' => 200]);
    }
}
