<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UsersAreaIdPatchRequest;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\UsersEmailPostRequest;
use App\Services\EmailChangeService;
use App\Http\Requests\Api\UsersPasswordPatchRequest;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected EmailChangeService $emailChangeService
    ) {
    }

    /**
     * 会員の地域IDを更新する
     */
    public function updateAreaId(UsersAreaIdPatchRequest $request)
    {
        $user = $this->userService->updateAreaId(Auth::user(), $request->area_id);
        return response()->json([
            'user' => $this->userService->getUserStoreState($user),
        ]);
    }

    /**
     * メールアドレスの変更申請を登録する
     */
    public function requestEmailChange(UsersEmailPostRequest $request)
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
     *
     */
    public function updatePassword(UsersPasswordPatchRequest $request)
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
