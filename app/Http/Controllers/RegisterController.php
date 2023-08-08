<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Requests\RegisterPostRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\UserRegisterToken;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function __construct(
        protected UserService $userService
    )
    {
    }

    /**
     * 新規会員登録ページの表示
     */
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('register.show');
    }

    /**
     * 新規会員登録
     */
    public function register(RegisterPostRequest $request): View|RedirectResponse
    {
        if (!$this->userService->register($request->email, $request->password)) {
            return back()->withInput()->with('alert', ['msg' => '情報の登録に失敗しました。', 'type' => 'error']);
        }

        return view('register.mail_sent');
    }

    /**
     * 確認URL
     */
    public function complete(string $token): View
    {
        // トークンが長すぎる場合は不正なURL扱いにする
        if (mb_strlen($token) > 255) {
            return view('expired_url');
        }

        $userRegisterToken = UserRegisterToken::where('token', $token)->where('expires_at', '>=', date('Y-m-d H:i:s'))->first();
        
        if (empty($userRegisterToken)) {
            return view('expired_url');
        }
        
        $user = $userRegisterToken->user;

        if (empty($user)) {
            return view('expired_url');
        }

        if (!$this->userService->completeRegistration($user, $userRegisterToken->id)) {
            return view('register.fail');
        }

        return view('register.complete');
    }
}
