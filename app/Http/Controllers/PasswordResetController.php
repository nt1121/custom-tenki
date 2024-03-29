<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordResetPostRequest;
use App\Http\Requests\PasswordResetRequestPostRequest;
use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService
    ) {
    }

    /**
     * パスワード再設定申請ページを表示する
     * 
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function showRequestPage(): View | RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('password_reset.request');
    }

    /**
     * パスワードの変更を申請する
     *
     * @param  App\Http\Requests\PasswordResetRequestPostRequest $request
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function request(PasswordResetRequestPostRequest $request): View | RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        $user = User::where('email', $request->email)->whereNotNull('email_verified_at')->first();

        // データベースの外部キー制約があるため、基本的には紐づくユーザーが登録されていないことはありえない
        if (empty($user)) {
            // メールアドレスが一致する会員がいない場合は成功扱い
            return view('password_reset.request_complete');
        }

        if (!$this->passwordResetService->createRequest($user->id, $user->email)) {
            return back()->withInput()->with('alert', ['msg' => '情報の登録に失敗しました。', 'type' => 'error']);
        }

        return view('password_reset.request_complete');
    }

    /**
     * トークンを確認し新しいパスワードの入力画面を表示
     * 
     * @param  string $token 確認URLのトークン
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function showResetPage(string $token): View | RedirectResponse
    {
        $passwordResetRequest = PasswordResetRequest::where('token', $token)->where('expires_at', '>=', date('Y-m-d H:i:s'))->first();

        if (empty($passwordResetRequest)) {
            return view('expired_url');
        }

        $user = $passwordResetRequest->user;

        // データベースの外部キー制約があるため、基本的には紐づくユーザーが登録されていないことはありえない
        if (empty($user)) {
            return view('expired_url');
        }

        $loginUser = Auth::user();

        if ($loginUser) {
            if ($loginUser->id === $user->id) {
                return redirect('/weather');
            } else {
                return view('need_logout', ['isPasswordReset' => true]);
            }
        }

        return view('password_reset.reset', ['token' => $token]);
    }

    /**
     * パスワードをリセットする
     * 
     * @param  App\Http\Requests\PasswordResetPostRequest $request
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function reset(PasswordResetPostRequest $request): View | RedirectResponse
    {
        if (!$request->has('token')) {
            return back()->with('alert', ['msg' => 'トークンがありません。', 'type' => 'error']);
        }

        $passwordResetRequest = PasswordResetRequest::where('token', $request->token)->where('expires_at', '>=', date('Y-m-d H:i:s'))->first();

        if (empty($passwordResetRequest)) {
            return view('expired_url');
        }

        $user = $passwordResetRequest->user;

        // データベースの外部キー制約があるため、基本的には紐づくユーザーが登録されていないことはありえない
        if (empty($user)) {
            return view('expired_url');
        }

        $loginUser = Auth::user();

        if ($loginUser) {
            if ($loginUser->id !== $user->id) {
                return view('need_logout', ['isPasswordReset' => true]);
            }
        }

        if (!$this->passwordResetService->reset($user, $request->password, $passwordResetRequest->id)) {
            return back()->with('alert', ['msg' => '情報の更新に失敗しました。', 'type' => 'error']);
        }

        return view('password_reset.complete');
    }
}
