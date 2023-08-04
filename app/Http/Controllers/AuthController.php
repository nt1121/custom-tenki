<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\UserRegisterToken;
use Illuminate\Support\Facades\Hash;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\RegisterPostRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\LoginPostRequest;
use App\Services\AuthService;
use App\Models\PasswordResetRequest;
use App\Http\Requests\PasswordResetPostRequest;
use App\Http\Requests\PasswordResetRequestPostRequest;

class AuthController extends Controller
{
    /**
     * ログインページの表示
     */
    public function showLoginPage(): View|RedirectResponse
    {
        /* ログアウトできないため一旦コメントアウト
        if (Auth::check()) {
            return redirect('/weather');
        }
        */

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
        ], $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/'); // TODO 後で/weatherに変更する
        }

        return back()->withErrors(['email' => 'メールアドレスまたはパスワードが間違っています。'])
            ->withInput($request->except('password'));
    }
    
    /**
     * ログアウト
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
    
    /**
     * 新規会員登録ページの表示
     */
    public function showRegisterPage(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('register');
    }

    /**
     * 新規会員登録
     */
    public function register(RegisterPostRequest $request): View|RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = User::create(['email' => $request->email, 'password' => Hash::make($request->password)]);
            $token = AuthService::generateTokenForVerificationUrl($user->id);
            $expiresAt = new Carbon('+24 hours');
            UserRegisterToken::create(['user_id' => $user->id, 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
            $url = config('app.url') . '/register/' . $token;
            $text = <<<END
CustomTenkiにご登録いただき、ありがとうございます。

24時間以内に下記のURLをクリックして会員登録を完了してください。
{$url}

※URLが有効期限切れの場合はもう一度新規会員登録画面から登録をお願いいたします。
※有効期限内に上記のURLへのアクセスがない場合、一定時間後に会員情報は削除されます。
※CustomTenkiに会員登録をした覚えがない場合は、お手数ですがこのメールを破棄くださいますようお願い申し上げます。
END;
            Mail::to($user->email)->send(new NotificationMail('【CustomTenki】メールアドレスの確認', $text));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->withInput()->with('alert', ['msg' => '情報の登録に失敗しました。', 'type' => 'error']);
        }

        return view('register_finish');
    }

    /**
     * 確認URL
     */
    public function verifyUserRegistrationToken(string $token): View|RedirectResponse
    {
        $now = date('Y-m-d H:i:s');
        $userRegisterToken = UserRegisterToken::where('token', $token)->where('expires_at', '>=', $now)->first();
        
        if (empty($userRegisterToken)) {
            return view('expired_url');
        }
        
        $user = $userRegisterToken->user;

        if (empty($user)) {
            return view('expired_url');
        }

        try {
            DB::beginTransaction();
            $user->email_verified_at = $now;
            $user->save();
            $userRegisterToken->delete();
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return view('register_verify_fail');
        }

        Auth::login($user);
        return redirect('/'); // TODO /weatherに変更する
    }

    /**
     * パスワード再設定申請ページを表示する
     */
    public function showPasswordResetRequestPage(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('password_reset_request');
    }

    /**
     * パスワードの変更を申請する
     * 
     * PasswordResetRequest
     */
    public function requestPasswordReset(PasswordResetRequestPostRequest $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        $user = User::where('email', $request->email)->whereNotNull('email_verified_at')->first();

        if (empty($user)) {
            // メールアドレスが一致する会員がいない場合は成功扱い
            return view('password_reset_request_finish');
        }

        try {
            DB::beginTransaction();
            $token = AuthService::generateTokenForVerificationUrl($user->id);
            $expiresAt = new Carbon('+24 hours');
            PasswordResetRequest::updateOrCreate(['user_id' => $user->id], ['token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
            $url = config('app.url') . '/password_reset/' . $token;
            $text = <<<END
パスワードの再設定画面よりパスワードの再設定の申請を受付けました。

24時間以内に下記のURLをクリックしてパスワードの再設定を行なってください。
{$url}

※URLが有効期限切れの場合はもう一度パスワード再設定画面から申請をお願いいたします。
※このメールの送信後にパスワード再設定画面からのパスワードの再設定の申請が行われた場合、上記のURLは無効になります。
※CustomTenkiに会員登録をした覚えがない場合は、お手数ですがこのメールを破棄くださいますようお願い申し上げます。
END;
            Mail::to($user->email)->send(new NotificationMail('【CustomTenki】メールアドレスの確認', $text));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->withInput()->with('alert', ['msg' => '情報の登録に失敗しました。', 'type' => 'error']);
        }

        return view('password_reset_request_finish');
    }

    /**
     * パスワードリセット用のトークンの確認
     */
    public function verifyPasswordResetRequestToken(string $token): View|RedirectResponse
    {
        $passwordResetRequest = PasswordResetRequest::where('token', $token)->where('expires_at', '>=', date('Y-m-d H:i:s'))->first();

        if (empty($passwordResetRequest)) {
            return view('expired_url');
        }

        $user = $passwordResetRequest->user;

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

        return view('password_reset', ['token' => $token]);
    }

    /**
     * パスワードをリセットする
     */
    public function resetPassword(PasswordResetPostRequest $request): View|RedirectResponse
    {
        if (!$request->has('token')) {
            return back()->with('alert', ['msg' => 'トークンがありません。', 'type' => 'error']);
        }

        $passwordResetRequest = PasswordResetRequest::where('token', $request->token)->where('expires_at', '>=', date('Y-m-d H:i:s'))->first();

        if (empty($passwordResetRequest)) {
            return view('expired_url');
        }

        $user = $passwordResetRequest->user;

        if (empty($user)) {
            return view('expired_url');
        }

        $loginUser = Auth::user();

        if ($loginUser) {
            if ($loginUser->id !== $user->id) {
                return view('need_logout', ['isPasswordReset' => true]);
            }
        }

        try {
            DB::beginTransaction();
            $user->password = Hash::make($request->password);
            $user->save();
            $passwordResetRequest->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with('alert', ['msg' => '情報の更新に失敗しました。', 'type' => 'error']);
        }

        return view('password_reset_finish');
    }
}
