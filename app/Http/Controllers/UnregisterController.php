<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnregisterDeleteRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UnregisterController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * アカウント削除画面の表示
     * 
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function show(): View | RedirectResponse
    {
        $loginUser = Auth::user();

        if ($loginUser->is_test_user) {
            return redirect('/weather');
        }

        return view('unregister.show', ['loginUserId' => $loginUser->id]);
    }

    /**
     * アカウントを削除する
     * 
     * @param  App\Http\Requests\UnregisterDeleteRequest
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function unregister(UnregisterDeleteRequest $request): View | RedirectResponse
    {
        $loginUser = Auth::user();

        if (!Hash::check($request->password, $loginUser->password)) {
            return back()->withErrors(['password' => 'パスワードが間違っています。']);
        }

        if (!$this->userService->unregister($loginUser->id, $loginUser->email)) {
            return back()->with('alert', ['msg' => 'アカウントの削除に失敗しました。', 'type' => 'error']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return view('unregister.complete');
    }
}
