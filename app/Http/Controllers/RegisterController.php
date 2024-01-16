<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterPostRequest;
use App\Models\User;
use App\Models\UserRegisterToken;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * 新規会員登録ページの表示
     * 
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function show(): View | RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('register.show');
    }

    /**
     * 新規会員登録
     * 
     * @param  App\Http\Requests\RegisterPostRequest $request
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function register(RegisterPostRequest $request): View | RedirectResponse
    {
        if (!$this->userService->register($request->email, $request->password)) {
            return back()->withInput()->with('alert', ['msg' => '情報の登録に失敗しました。', 'type' => 'error']);
        }

        return view('register.mail_sent');
    }

    /**
     * 確認URL
     * 
     * @param  stirng $token　確認URLのトークン
     * @return Illuminate\View\View
     */
    public function complete(string $token, Request $request): View
    {
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

        Auth::login($user);
        $request->session()->regenerate();
        return view('register.complete');
    }
}
