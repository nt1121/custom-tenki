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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\LoginPostRequest;

class AuthController extends Controller
{
    /**
     * ログインページの表示
     */
    public function showLoginPage(): View
    {
        // TODO ログイン済みの場合はリダイレクトさせたい

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
    public function showRegisterPage(): View
    {
        // ログイン済みの場合はリダイレクトさせたい

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
            $token = dechex($user->id) . '-' . Str::uuid();
            UserRegisterToken::create(['user_id' => $user->id, 'token' => $token]);
            $url = config('app.url') . '/register/verify/' . $token;
            $text = <<<END
CustomTenkiにご登録いただき、ありがとうございます。

24時間以内に下記のURLをクリックして会員登録を完了してください。
{$url}

※URLが有効期限切れの場合はもう一度新規会員登録画面から登録をお願いいたします。
※有効期限ないに上記のURLへのアクセスがない場合、一定時間後にご入力いただいた会員情報は削除されます。
※CustomTenkiに会員登録をした覚えがない場合は、お手数ですがこのメールを破棄くださいますようお願い申し上げます。
END;
            Mail::to($user->email)->send(new NotificationMail('【CustomTenki】メールアドレスの確認', $text));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect('/')->withInput()->with('alert', ['msg' => '登録に失敗しました。', 'type' => 'error']);
        }

        return view('register_finish');
    }

    /**
     * 確認URL
     */
    public function verifyUserRegistrationToken(string $token): View|RedirectResponse
    {
        $dt = new Carbon('-24 hours');
        $userRegisterToken = UserRegisterToken::where('token', $token)->where('created_at', '>=', $dt->format('Y-m-d H:i:s'))->first();
        
        if (empty($userRegisterToken)) {
            return view('expired_url');
        }
        
        $user = $userRegisterToken->user;

        if (empty($user)) {
            return view('expired_url');
        }

        try {
            DB::beginTransaction();
            $user->email_verified_at = date('Y-m-d H:i:s');
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
}
