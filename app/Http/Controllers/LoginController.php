<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginPostRequest;
use App\Http\Requests\TestUserLoginPostRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * ログインページの表示
     *
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function show(): View | RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('login');
    }

    /**
     * ログイン
     * 
     * @param  App\Http\Requests\LoginPostRequest $request
     * @return Illuminate\Http\RedirectResponse
     */
    public function login(LoginPostRequest $request): RedirectResponse
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            fn(Builder $query) => $query->whereNotNull('email_verified_at'),
        ], $request->input('remember', false))) {
            $request->session()->regenerate();
            return redirect()->intended('/weather');
        }

        return back()->withErrors(['email' => 'メールアドレスまたはパスワードが間違っています。'])
            ->withInput($request->except('password'));
    }

    /**
     * テストユーザーとしてログイン
     * 
     * @param  App\Http\Requests\TestUserLoginPostRequest $request
     * @return Illuminate\Http\RedirectResponse
     */
    public function loginAsTestUser(TestUserLoginPostRequest $request): RedirectResponse
    {
        $testUser = User::where('is_test_user', true)->first();

        if (empty($testUser)) {
            return back()->with('alert', ['msg' => 'テストユーザーが登録されていません。', 'type' => 'error']);
        }

        Auth::login($testUser);
        $request->session()->regenerate();
        return redirect('/weather');
    }
}
