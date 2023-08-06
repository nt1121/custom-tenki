<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginPostRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * ログインページの表示
     */
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('login');
    }

    /**
     * ログイン
     */
    public function login(LoginPostRequest $request): RedirectResponse
    {
        if (Auth::attempt([
            'email' => $request->email, 
            'password' => $request->password, 
            fn (Builder $query) => $query->whereNotNull('email_verified_at'),
        ], $request->input('remember', false))) {
            $request->session()->regenerate();
            return redirect()->intended('/weather');
        }

        return back()->withErrors(['email' => 'メールアドレスまたはパスワードが間違っています。'])
            ->withInput($request->except('password'));
    }
}