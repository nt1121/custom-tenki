<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

use Tests\TestCase;

use App\Models\User;
use App\Models\EmailChangeRequest;
use App\Models\UserRegisterToken;
use App\Models\PasswordResetRequest;
use App\Models\Area;
use App\Models\AreaGroup;
use App\Models\WeatherForecastItem;

class HttpTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
    }

    /**
     * /へのGETリクエスト
     */
    public function test_get_top(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('表示する項目などがカスタマイズできる天気予報です。');
    }

    /**
     * /termsへのGETリクエスト
     */
    public function test_get_terms(): void
    {
        $response = $this->get('/terms');
        $response->assertStatus(200);
        $response->assertSee('利用規約');
    }

    /**
     * /privacyへのGETリクエスト
     */
    public function test_get_privacy(): void
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200);
        $response->assertSee('プライバシーポリシー');
    }

    /**
     * /test_user_loginへのPOSTリクエスト1（テストユーザーが登録されていない場合）
     */
    public function test_post_test_user_login1(): void
    {
        $response = $this->post('/test_user_login');
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /**
     * /test_user_loginへのPOSTリクエスト2（テストユーザーが登録されている場合）
     */
    public function test_post_test_user_login2(): void
    {
        // テストユーザーを登録する
        DB::table('users')->delete(); // usersテーブルのレコードを全件削除
        $user = new User;
        $user->email = config('const.test_user_email1');
        $user->password = Hash::make('test1234');
        $user->is_test_user = true;
        $user->save();
        $response = $this->post('/test_user_login');
        $response->assertStatus(302);
        $response->assertRedirect('/weather');
    }

    /**
     * /loginへのGETリクエスト1（未ログイン）
     */
    public function test_get_login1(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ログイン');
    }

    /**
     * /loginへのGETリクエスト2（ログイン中）
     */
    public function test_get_login2(): void
    {
        DB::table('users')->delete(); // usersテーブルのレコードを全件削除
        $user = new User;
        $user->email = config('const.test_user_email1');
        $user->password = Hash::make('test1234');
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();
        Auth::login($user);
        $response = $this->get('/login');
        $response->assertStatus(302);
        $response->assertRedirect('/weather');
        Auth::logout();
    }

    /**
     * /loginへのPOSTリクエスト1（バリデーションエラー）
     */
    public function test_post_login1(): void
    {
        DB::table('users')->delete(); // usersテーブルのレコードを全件削除
        $user = new User;
        $user->email = config('const.test_user_email1');
        $user->password = Hash::make('test1234');
        $user->save();
        // emailがない
        $response = $this->post('/login', ['password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
        $response->assertValid(['password']);
    }

    /**
     * /loginへのPOSTリクエスト2（バリデーションエラー）
     */
    public function test_post_login2(): void
    {
        // emailがメールアドレスの形式ではない
        $response = $this->post('/login', ['email' => 'a', 'password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
        $response->assertValid(['password']);
    }

    /**
     * /loginへのPOSTリクエスト3（バリデーションエラー）
     */
    public function test_post_login3(): void
    {
        // emailが150文字を超えている
        $response = $this->post('/login', [
            'email' => 'loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong@example.com',
            'password' => 'test1234'
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
        $response->assertValid(['password']);
    }

    /**
     * /loginへのPOSTリクエスト4（バリデーションエラー）
     */
    public function test_post_login4(): void
    {
        // passwordがない
        $response = $this->post('/login', ['email' => config('const.test_user_email1')]);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
        $response->assertValid(['email']);
    }

    /**
     * /loginへのPOSTリクエスト5（バリデーションエラー）
     */
    public function test_post_login5(): void
    {
        // passwordが8文字未満
        $response = $this->post('/login', ['email' => config('const.test_user_email1'), 'password' => 'test123']);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
        $response->assertValid(['email']);
    }

    /**
     * /loginへのPOSTリクエスト6（バリデーションエラー）
     */
    public function test_post_login6(): void
    {
        // passwordが256文字以上
        $response = $this->post('/login', [
            'email' => config('const.test_user_email1'), 
            'password' => 'test123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012'
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
        $response->assertValid(['email']);
    }

    /**
     * /loginへのPOSTリクエスト7（バリデーションエラー）
     */
    public function test_post_login7(): void
    {
        // passwordが正規表現チェックエラー
        $response = $this->post('/login', ['email' => config('const.test_user_email1'), 'password' => 'test-1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
        $response->assertValid(['email']);
    }

    /**
     * /loginへのPOSTリクエスト8（バリデーションエラー）
     */
    public function test_post_login8(): void
    {
        // メールアドレス未認証のためログインできない
        $response = $this->post('/login', ['email' => config('const.test_user_email1'), 'password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['email' => 'メールアドレスまたはパスワードが間違っています。',]);
        $response->assertValid(['password']);
    }

    /**
     * /loginへのPOSTリクエスト9（ログイン成功）
     */
    public function test_post_login9(): void
    {
        DB::table('users')->delete(); // usersテーブルのレコードを全件削除
        $user = new User;
        $user->email = config('const.test_user_email1');
        $user->password = Hash::make('test1234');
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();
        $response = $this->post('/login', ['email' => config('const.test_user_email1'), 'password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertValid(['email', 'password']);
        $response->assertRedirect('/weather');
    }

    /**
     * /logoutへのPOSTリクエスト
     */
    public function test_post_logout(): void
    {
        $response = $this->post('/logout');
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /**
     * /registerへのGETリクエスト1（未ログイン）
     */
    public function test_get_register1(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('新規会員登録');
    }

    /**
     * /registerへのGETリクエスト2（ログイン中）
     */
    public function test_get_register2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user); // ログイン
        $response = $this->get('/register');
        $response->assertStatus(302);
        $response->assertRedirect('/weather');
        Auth::logout(); // ログアウト
    }

    /**
     * /registerへのPOSTリクエスト1（バリデーションエラー）
     */
    public function test_post_register1(): void
    {
        // emailがメールアドレスの形式ではない
        $response = $this->post('/register', ['email' => 'a', 'password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
        $response->assertValid(['password']);
    }

    /**
     * /registerへのPOSTリクエスト2（バリデーションエラー）
     */
    public function test_post_register2(): void
    {
        // emailが150文字を超えている
        $response = $this->post('/register', [
            'email' => 'loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong@example.com',
            'password' => 'test1234'
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
        $response->assertValid(['password']);
    }

    /**
     * /registerへのPOSTリクエスト3（バリデーションエラー）
     */
    public function test_post_register3(): void
    {
        // emailがusersに存在する
        $response = $this->post('/register', ['email' => config('const.test_user_email1'), 'password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
        $response->assertValid(['password']);
    }

    /**
     * /registerへのPOSTリクエスト4（バリデーションエラー）
     */
    public function test_post_register4(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $token = \Common::generateConfirmationUrlToken($user->id);
        $expiresAt = new Carbon('+24 hours');
        $emailChangeRequest = EmailChangeRequest::create(['user_id' => $user->id, 'email' => config('const.test_user_email2'), 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        // emailがemail_change_requestsに存在する
        $response = $this->post('/register', ['email' => config('const.test_user_email2'), 'password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
        $response->assertValid(['password']);
        $emailChangeRequest->delete();
    }

    /**
     * /registerへのPOSTリクエスト5（バリデーションエラー）
     */
    public function test_post_register5(): void
    {
        // passwordがない
        $response = $this->post('/register', ['email' => config('const.test_user_email2')]);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
        $response->assertValid(['email']);
    }

    /**
     * /registerへのPOSTリクエスト6（バリデーションエラー）
     */
    public function test_post_register6(): void
    {
        // passwordが8文字未満
        $response = $this->post('/register', ['email' => config('const.test_user_email2'), 'password' => 'test123']);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
        $response->assertValid(['email']);
    }

    /**
     * /registerへのPOSTリクエスト7（バリデーションエラー）
     */
    public function test_post_register7(): void
    {
        // passwordが256文字以上
        $response = $this->post('/register', [
            'email' => config('const.test_user_email2'), 
            'password' => 'test123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012'
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
        $response->assertValid(['email']);
    }

    /**
     * /registerへのPOSTリクエスト8（バリデーションエラー）
     */
    public function test_post_register8(): void
    {
        // passwordが正規表現チェックエラー
        $response = $this->post('/register', ['email' => config('const.test_user_email2'), 'password' => 'test-1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
        $response->assertValid(['email']);
    }

    /**
     * /registerへのPOSTリクエスト9（登録成功）
     */
    public function test_post_register9(): void
    {
        // passwordが正規表現チェックエラー
        $response = $this->post('/register', ['email' => config('const.test_user_email2'), 'password' => 'test1234']);
        $response->assertStatus(200);
        $response->assertValid(['email', 'password']);
        $response->assertSee('新規会員登録');
    }

    // \Common::generateConfirmationUrlToken(99999999);

    /**
     * /register/{token}へのGETリクエスト1（無効なURL）
     */
    public function test_get_register_token1(): void
    {
        $token = \Common::generateConfirmationUrlToken(99999999);
        $response = $this->get('/register/' . $token);
        $response->assertStatus(200);
        $response->assertSee('無効なURL');
    }

    /**
     * /register/{token}へのGETリクエスト3（登録完了）
     */
    public function test_get_register_token2(): void
    {
        DB::table('users')->delete();
        DB::table('user_register_tokens')->delete();
        $user = User::create(['email' => config('const.test_user_email2'), 'password' => Hash::make('test1234')]);
        $token = \Common::generateConfirmationUrlToken($user->id);
        $expiresAt = new Carbon('+24 hours');
        UserRegisterToken::create(['user_id' => $user->id, 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $response = $this->get('/register/' . $token);
        $response->assertStatus(200);
        $response->assertSee('新規会員登録');
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(3, $user->weatherForecastItems->count());
    }

    /**
     * /password_reset/requestへのGETリクエスト1（未ログイン）
     */
    public function test_get_password_reset_request1(): void
    {
        $response = $this->get('/password_reset/request');
        $response->assertStatus(200);
    }

    /**
     * /password_reset/requestへのGETリクエスト2（ログイン中）
     */
    public function test_get_password_reset_request2(): void
    {
        $user = new User;
        $user->email = config('const.test_user_email1');
        $user->password = Hash::make('test1234');
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();
        Auth::login($user); // ログイン
        $response = $this->get('/password_reset/request');
        $response->assertStatus(302);
        $response->assertRedirect('/weather');
        Auth::logout(); // ログアウト
    }

    /**
     * /password_reset/requestへのPOSTリクエスト1（バリデーションエラー）
     */
    public function test_post_password_reset_request1(): void
    {
        // emailがない
        $response = $this->post('/password_reset/request');
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
    }

    /**
     * /password_reset/requestへのPOSTリクエスト2（バリデーションエラー）
     */
    public function test_post_password_reset_request2(): void
    {
        // emailがメールアドレスの形式ではない
        $response = $this->post('/password_reset/request', ['email' => 'a']);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
    }

    /**
     * /password_reset/requestへのPOSTリクエスト3（バリデーションエラー）
     */
    public function test_post_password_reset_request3(): void
    {
        // emailが150文字を超えている
        $response = $this->post('/password_reset/request', [
            'email' => 'loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong@example.com'
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['email']);
    }

    /**
     * /password_reset/requestへのPOSTリクエスト4（ログイン中）
     */
    public function test_post_password_reset_request4(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user); // ログイン
        $response = $this->post('/password_reset/request', ['email' => config('const.test_user_email1')]);
        $response->assertStatus(302);
        $response->assertRedirect('/weather');
        Auth::logout(); // ログアウト
    }

    /**
     * /password_reset/requestへのPOSTリクエスト5（登録されていないメールアドレス）
     */
    public function test_post_password_reset_request5(): void
    {
        $user = User::where('email', config('const.test_user_email2'))->first();
        if ($user) $user->delete();
        DB::table('password_reset_requests')->delete();
        $response = $this->post('/password_reset/request', ['email' => config('const.test_user_email2')]);
        $response->assertStatus(200);
        $response->assertSee('パスワードの再設定');
        // PasswordResetRequestが登録されていないことを確認する
        $this->assertNull(PasswordResetRequest::where('user_id', $user->id)->first());
    }

    /**
     * /password_reset/requestへのPOSTリクエスト6（登録されているメールアドレス）
     */
    public function test_post_password_reset_request6(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        DB::table('password_reset_requests')->delete();
        $response = $this->post('/password_reset/request', ['email' => config('const.test_user_email1')]);
        $response->assertStatus(200);
        $response->assertSee('パスワードの再設定');
        // PasswordResetRequestが登録されていることを確認する
        $this->assertNotNull(PasswordResetRequest::where('user_id', $user->id)->first());
    }

    /**
     * /password_reset/{token}へのGETリクエスト1（無効なURL）
     */
    public function test_post_password_reset_token1(): void
    {
        $token = \Common::generateConfirmationUrlToken(99999999);
        $response = $this->get('/password_reset/' . $token);
        $response->assertStatus(200);
        $response->assertSee('無効なURL');
    }

    /**
     * /password_reset/{token}へのGETリクエスト3（同じユーザーでログイン中）
     */
    public function test_post_password_reset_token2(): void
    {
        DB::table('users')->delete();
        DB::table('password_reset_requests')->delete();
        $user = new User;
        $user->email = config('const.test_user_email1');
        $user->password = Hash::make('test1234');
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();
        $token = \Common::generateConfirmationUrlToken($user->id);
        $expiresAt = new Carbon('+24 hours');
        $passwordResetRequest = PasswordResetRequest::updateOrCreate(['user_id' => $user->id], ['token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        Auth::login($user);
        $response = $this->get('/password_reset/' . $passwordResetRequest->token);
        $response->assertStatus(302);
        $response->assertRedirect('/weather');
        Auth::logout();
    }

    /**
     * /password_reset/{token}へのGETリクエスト4（別のユーザーでログイン中）
     */
    public function test_post_password_reset_token3(): void
    {
        $user1 = User::where('email', config('const.test_user_email1'))->first();
        $passwordResetRequest = PasswordResetRequest::where('user_id', $user1->id)->first();
        $user2 = new User;
        $user2->email = config('const.test_user_email2');
        $user2->password = Hash::make('test1234');
        $user2->email_verified_at = date('Y-m-d H:i:s');
        $user2->save();
        Auth::login($user2);
        $response = $this->get('/password_reset/' . $passwordResetRequest->token);
        $response->assertStatus(200);
        $response->assertSee('現在別の会員でログイン中です。');
        Auth::logout();
    }

    /**
     * /password_reset/{token}へのGETリクエスト5（未ログイン）
     */
    public function test_post_password_reset_token4(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $passwordResetRequest = PasswordResetRequest::where('user_id', $user->id)->first();
        $response = $this->get('/password_reset/' . $passwordResetRequest->token);
        $response->assertStatus(200);
    }

    /**
     * /password_resetへのPATCHリクエスト1
     */
    public function test_patch_password_reset1(): void
    {
        $response = $this->patch('/password_reset');
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
    }

    /**
     * /password_resetへのPATCHリクエスト2
     */
    public function test_patch_password_reset2(): void
    {
        // passwordが8文字未満
        $response = $this->patch('/password_reset', ['email' => config('const.test_user_email2'), 'password' => 'test123']);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
    }

    /**
     * /password_resetへのPATCHリクエスト3
     */
    public function test_patch_password_reset3(): void
    {
        // passwordが256文字以上
        $response = $this->patch('/password_reset', [
            'password' => 'test123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012'
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
    }

    /**
     * /password_resetへのPATCHリクエスト4
     */
    public function test_patch_password_reset4(): void
    {
        // passwordが正規表現チェックエラー
        $response = $this->patch('/password_reset', ['password' => 'test-1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['password']);
    }

    /**
     * /password_resetへのPATCHリクエスト5
     */
    public function test_patch_password_reset5(): void
    {
        // tokenがない
        $response = $this->patch('/password_reset', ['password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertValid(['password']);
    }

    /**
     * /password_resetへのPATCHリクエスト6
     */
    public function test_patch_password_reset6(): void
    {
        // tokenが間違っている
        $token = \Common::generateConfirmationUrlToken(99999999);
        $response = $this->patch('/password_reset', ['password' => 'test1234', 'token' => $token]);
        $response->assertStatus(200);
        $response->assertSee('無効なURL');
    }

    /**
     * /password_resetへのPATCHリクエスト8
     */
    public function test_patch_password_reset7(): void
    {
        DB::table('users')->delete();
        DB::table('password_reset_requests')->delete();
        $user1 = new User;
        $user1->email = config('const.test_user_email1');
        $user1->password = Hash::make('test1234');
        $user1->email_verified_at = date('Y-m-d H:i:s');
        $user1->save();
        $token = \Common::generateConfirmationUrlToken($user1->id);
        $expiresAt = new Carbon('+24 hours');
        $passwordResetRequest = PasswordResetRequest::updateOrCreate(['user_id' => $user1->id], ['token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $user2 = new User;
        $user2->email = config('const.test_user_email2');
        $user2->password = Hash::make('test1234');
        $user2->email_verified_at = date('Y-m-d H:i:s');
        $user2->save();
        // 別のユーザーでログイン中
        Auth::login($user2);
        $response = $this->patch('/password_reset', ['password' => 'test1234', 'token' => $passwordResetRequest->token]);
        $response->assertStatus(200);
        $response->assertSee('現在別の会員でログイン中です。');
        Auth::logout();
    }

    /**
     * /password_resetへのPATCHリクエスト9
     */
    public function test_patch_password_reset8(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $passwordResetRequest = PasswordResetRequest::where('user_id', $user->id)->first();
        $newPassword = 'test5678';
        $response = $this->patch('/password_reset', ['password' => $newPassword, 'token' => $passwordResetRequest->token]);
        $response->assertStatus(200);
        $response->assertSee('パスワードの再設定が完了しました。');
        $user->refresh();
        $this->assertTrue(password_verify($newPassword, $user->password));
    }

    /**
     * /email_change/{token}へのGETリクエスト1
     */
    public function test_get_email_change_token1(): void
    {
        $token = \Common::generateConfirmationUrlToken(99999999);
        // tokenが間違っている場合
        $response = $this->get('/email_change/' . $token);
        $response->assertStatus(200);
        $response->assertSee('無効なURL');
    }

    /**
     * /email_change/{token}へのGETリクエスト2
     */
    public function test_get_email_change_token2(): void
    {
        DB::table('users')->delete();
        DB::table('password_reset_requests')->delete();
        $user1 = new User;
        $user1->email = config('const.test_user_email1');
        $user1->password = Hash::make('test1234');
        $user1->email_verified_at = date('Y-m-d H:i:s');
        $user1->save();
        $token = \Common::generateConfirmationUrlToken($user1->id);
        $expiresAt = new Carbon('+24 hours');
        $emailChangeRequest = EmailChangeRequest::updateOrCreate(['user_id' => $user1->id], ['email' => config('const.test_user_email2'), 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $user2 = new User;
        $user2->email = 'test@example.com';
        $user2->password = Hash::make('test1234');
        $user2->email_verified_at = date('Y-m-d H:i:s');
        $user2->save();
        // 別のユーザーでログイン中
        Auth::login($user2);
        $response = $this->get('/email_change/' . $emailChangeRequest->token);
        $response->assertStatus(200);
        $response->assertSee('現在別の会員でログイン中です。');
        Auth::logout();
    }

    /**
     * /email_change/{token}へのGETリクエスト3
     */
    public function test_get_email_change_token3(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $emailChangeRequest = EmailChangeRequest::where('user_id', $user->id)->first();
        $response = $this->get('/email_change/' . $emailChangeRequest->token);
        $response->assertStatus(200);
        $response->assertSee('メールアドレスの変更');
        $user->refresh();
        $this->assertSame(config('const.test_user_email2'), $user->email);
    }

    /**
     * /weatherへのGETリクエスト
     */
    public function test_get_weather(): void
    {
        $user = User::where('email', config('const.test_user_email2'))->first();
        $user->email = config('const.test_user_email1');
        $user->save();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/weather');
        $response->assertStatus(200);
        Auth::logout();
    }

    /**
     * /unregisterへのGETリクエスト1
     */
    public function test_get_unregister1(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $user->is_test_user = true;
        $user->save();
        Auth::login($user);
        // テストユーザーの場合
        $response = $this->actingAs($user)->get('/unregister');
        $response->assertStatus(302);
        $response->assertRedirect('/weather');
        Auth::logout();
    }

    /**
     * /unregisterへのGETリクエスト2
     */
    public function test_get_unregister2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $user->is_test_user = false;
        $user->save();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/unregister');
        $response->assertStatus(200);
        Auth::logout();
    }

    /**
     * /unregisterへのDELETEリクエスト1
     */
    public function test_delete_unregister1(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->delete('/unregister', ['password' => 'test1234']);
        $response->assertStatus(302);
        $response->assertInvalid(['user_id']);
        Auth::logout();
    }

    /**
     * /unregisterへのDELETEリクエスト2
     */
    public function test_delete_unregister2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->delete('/unregister', ['user_id' => 99999999, 'password' => 'test1234']);
        $response->assertStatus(403);
        Auth::logout();
    }

    /**
     * /unregisterへのDELETEリクエスト3
     */
    public function test_delete_unregister3(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $user->is_test_user = true;
        $user->save();
        Auth::login($user);
        // テストユーザーの場合
        $response = $this->actingAs($user)->delete('/unregister', ['user_id' => $user->id, 'password' => 'test1234']);
        $response->assertStatus(403);
        Auth::logout();
        $user->is_test_user = false;
        $user->save();
    }

    /**
     * /unregisterへのDELETEリクエスト4
     */
    public function test_delete_unregister4(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->delete('/unregister', ['user_id' => $user->id]);
        $response->assertStatus(302);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['password']);
        Auth::logout();
    }

    /**
     * /unregisterへのDELETEリクエスト5
     */
    public function test_delete_unregister5(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // passwordが256文字以上
        $response = $this->actingAs($user)->delete('/unregister', [
            'user_id' => $user->id,
            'password' => 'test123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012'
        ]);
        $response->assertStatus(302);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['password']);
        Auth::logout();
    }

    /**
     * /unregisterへのDELETEリクエスト6
     */
    public function test_delete_unregister6(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // passwordが正規表現チェックエラー
        $response = $this->actingAs($user)->delete('/unregister', ['user_id' => $user->id, 'password' => 'test-1234']);
        $response->assertStatus(302);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['password']);
        Auth::logout();
    }

    /**
     * /unregisterへのDELETEリクエスト7
     */
    public function test_delete_unregister7(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // passwordが正規表現チェックエラー
        $response = $this->actingAs($user)->delete('/unregister', ['user_id' => $user->id, 'password' => 'test2345']);
        $response->assertStatus(302);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['password' => 'パスワードが間違っています。']);
        Auth::logout();
    }

    /**
     * /unregisterへのDELETEリクエスト8
     */
    public function test_delete_unregister8(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // passwordが正規表現チェックエラー
        $response = $this->actingAs($user)->delete('/unregister', ['user_id' => $user->id, 'password' => 'test1234']);
        $response->assertStatus(200);
        $response->assertValid(['user_id', 'password']);
        $response->assertSee('アカウントの削除');
        $this->assertNull(User::find($user->id));
        $this->assertFalse(Auth::check());
    }

    /**
     * /api/weatherへのGETリクエスト1
     */
    public function test_get_api_weather1(): void
    {
        $user = new User;
        $user->email = config('const.test_user_email1');
        $user->password = Hash::make('test1234');
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/weather');
        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'area', 'list']);
        $this->assertNotNull($response['user']);
        $this->assertNull($response['area']);
        $this->assertNull($response['list']);
        Auth::logout();
    }

    /**
     * /api/weatherへのGETリクエスト2
     */
    public function test_get_api_weather2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $area = Area::orderBy('id', 'asc')->first();
        $user->area_id = $area->id;
        $user->save();
        Auth::login($user);
        // エラーを発生させるためにAPIのエンドポイントを変更
        $oldApiEndpoint = config('const.weather_api.three_hour_forecast.endpoint');
        Config::set('const.weather_api.three_hour_forecast.endpoint', config('app.url') . '/api/test');
        $response = $this->actingAs($user)->get('/api/weather');
        $response->assertStatus(400);
        $response->assertJsonStructure(['status', 'errors']);
        Auth::logout();
        // APIのエンドポイントを元に戻す
        Config::set('const.weather_api.three_hour_forecast.endpoint', $oldApiEndpoint);
    }

    /**
     * /api/weatherへのGETリクエスト3
     */
    public function test_get_api_weather3(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/weather');
        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'area', 'list']);
        $this->assertNotNull($response['user']);
        $this->assertNotNull($response['area']);
        $this->assertNotNull($response['list']);
        Auth::logout();
    }

    /**
     * /api/settingsへのGETリクエスト
     */
    public function test_get_api_settings(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/settings');
        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'items_to_display']);
        $this->assertNotNull($response['user']);
        $this->assertNotNull($response['items_to_display']);
        Auth::logout();
    }

    /**
     * /api/settings/area_select/{id?}へのGETリクエスト1
     */
    public function test_get_api_settings_area_select1(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/settings/area_select/99999999');
        $response->assertStatus(404);
        $response->assertJsonStructure(['status', 'errors']);
        Auth::logout();
    }

    /**
     * /api/settings/area_select/{id?}へのGETリクエスト2
     */
    public function test_get_api_settings_area_select2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/settings/area_select');
        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'area_group']);
        $this->assertNull($response['area_group']['id']);
        $this->assertNull($response['area_group']['name']);
        Auth::logout();
    }

    /**
     * /api/settings/area_select/{id?}へのGETリクエスト3
     */
    public function test_get_api_settings_area_select3(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $areaGroup = AreaGroup::orderBy('id', 'asc')->first();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/settings/area_select/' . $areaGroup->id);
        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'area_group']);
        $this->assertNotNull($response['area_group']['id']);
        $this->assertNotNull($response['area_group']['name']);
        Auth::logout();
    }

    /**
     * /api/users/area_idへのPATCHリクエスト1
     */
    public function test_patch_api_users_area_id1(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $areaGroup1 = AreaGroup::orderBy('id', 'asc')->first();
        $areaGroup2 = AreaGroup::where('id', '<>', $areaGroup1->id)->orderBy('id', 'asc')->first();
        Auth::login($user);
        // user_idがない
        $response = $this->actingAs($user)->patch('/api/users/area_id', ['area_id' => $areaGroup2->id]);
        $response->assertStatus(400);
        $response->assertInvalid(['user_id']);
        $response->assertValid(['area_id']);
        Auth::logout();
    }

    /**
     * /api/users/area_idへのPATCHリクエスト2
     */
    public function test_patch_api_users_area_id2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $areaGroup1 = AreaGroup::orderBy('id', 'asc')->first();
        $areaGroup2 = AreaGroup::where('id', '<>', $areaGroup1->id)->orderBy('id', 'asc')->first();
        Auth::login($user);
        // user_idがログイン中のユーザーのIDと違う
        $response = $this->actingAs($user)->patch('/api/users/area_id', ['user_id' => 99999999, 'area_id' => $areaGroup2->id]);
        $response->assertStatus(403);
        Auth::logout();
    }

    /**
     * /api/users/area_idへのPATCHリクエスト3
     */
    public function test_patch_api_users_area_id3(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $areaGroup1 = AreaGroup::orderBy('id', 'asc')->first();
        $areaGroup2 = AreaGroup::where('id', '<>', $areaGroup1->id)->orderBy('id', 'asc')->first();
        Auth::login($user);
        // area_idがない
        $response = $this->actingAs($user)->patch('/api/users/area_id', ['user_id' => $user->id]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['area_id']);
        Auth::logout();
    }

    /**
     * /api/users/area_idへのPATCHリクエスト4
     */
    public function test_patch_api_users_area_id4(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $areaGroup1 = AreaGroup::orderBy('id', 'asc')->first();
        $areaGroup2 = AreaGroup::where('id', '<>', $areaGroup1->id)->orderBy('id', 'asc')->first();
        Auth::login($user);
        // area_idが整数ではない
        $response = $this->actingAs($user)->patch('/api/users/area_id', ['user_id' => $user->id, 'area_id' => 'a']);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['area_id']);
        Auth::logout();
    }

    /**
     * /api/users/area_idへのPATCHリクエスト5
     */
    public function test_patch_api_users_area_id5(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $areaGroup1 = AreaGroup::orderBy('id', 'asc')->first();
        $areaGroup2 = AreaGroup::where('id', '<>', $areaGroup1->id)->orderBy('id', 'asc')->first();
        Auth::login($user);
        // area_idが1未満
        $response = $this->actingAs($user)->patch('/api/users/area_id', ['user_id' => $user->id, 'area_id' => 0]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['area_id']);
        Auth::logout();
    }

    /**
     * /api/users/area_idへのPATCHリクエスト6
     */
    public function test_patch_api_users_area_id6(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $areaGroup1 = AreaGroup::orderBy('id', 'asc')->first();
        $areaGroup2 = AreaGroup::where('id', '<>', $areaGroup1->id)->orderBy('id', 'asc')->first();
        Auth::login($user);
        // area_idがDBに存在しない値
        $response = $this->actingAs($user)->patch('/api/users/area_id', ['user_id' => $user->id, 'area_id' => 99999999]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['area_id']);
        Auth::logout();
    }

    /**
     * /api/users/area_idへのPATCHリクエスト7
     */
    public function test_patch_api_users_area_id7(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $areaGroup1 = AreaGroup::orderBy('id', 'asc')->first();
        $user->area_id = $areaGroup1->id;
        $user->save();
        $areaGroup2 = AreaGroup::where('id', '<>', $areaGroup1->id)->orderBy('id', 'asc')->first();
        Auth::login($user);
        // area_idがない
        $response = $this->actingAs($user)->patch('/api/users/area_id', ['user_id' => $user->id, 'area_id' => $areaGroup2->id]);
        $response->assertStatus(200);
        $response->assertValid(['user_id', 'area_id']);
        $response->assertJsonStructure(['user']);
        $user->refresh();
        $this->assertSame($areaGroup2->id, $user->area_id);
        Auth::logout();
    }

    /**
     * /api/settings/item_selectへのGETリクエスト
     */
    public function test_get_api_settings_item_select(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/settings/item_select');
        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'items_to_display', 'items_to_hide']);
        $this->assertSame(0, count($response['items_to_display']));
        $this->assertSame(6, count($response['items_to_hide']));
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト1
     */
    public function test_api_user_weather_forecast_item1(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // user_idがない
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['item_ids_to_display' => $itemIdsToDisplay]);
        $response->assertStatus(400);
        $response->assertInvalid(['user_id']);
        $response->assertValid(['item_ids_to_display']);
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト2
     */
    public function test_api_user_weather_forecast_item2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // user_idがログインユーザーと違う
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['user_id' => 99999999, 'item_ids_to_display' => $itemIdsToDisplay]);
        $response->assertStatus(403);
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト3
     */
    public function test_api_user_weather_forecast_item3(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // item_ids_to_displayがない
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['user_id' => $user->id]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['item_ids_to_display']);
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト4
     */
    public function test_api_user_weather_forecast_item4(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // item_ids_to_displayが配列ではない
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['user_id' => $user->id, 'item_ids_to_display' => 'a']);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['item_ids_to_display']);
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト5
     */
    public function test_api_user_weather_forecast_item5(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // item_ids_to_displayが空の配列
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['user_id' => $user->id, 'item_ids_to_display' => []]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['item_ids_to_display']);
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト6
     */
    public function test_api_user_weather_forecast_item6(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // item_ids_to_displayの要素が整数ではない
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['user_id' => $user->id, 'item_ids_to_display' => ['a']]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['item_ids_to_display.0']);
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト7
     */
    public function test_api_user_weather_forecast_item7(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // item_ids_to_displayの要素が1未満
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['user_id' => $user->id, 'item_ids_to_display' => [0]]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['item_ids_to_display.0']);
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト8
     */
    public function test_api_user_weather_forecast_item8(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // item_ids_to_displayの要素が存在しない
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['user_id' => $user->id, 'item_ids_to_display' => [99999999]]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['item_ids_to_display.0']);
        Auth::logout();
    }

    /**
     * /api/user_weather_forecast_itemへのPUTリクエスト9
     */
    public function test_api_user_weather_forecast_item9(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $itemIdsToDisplay = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        Auth::login($user);
        // item_ids_to_displayの要素が存在しない
        $response = $this->actingAs($user)->put('/api/user_weather_forecast_item', ['user_id' => $user->id, 'item_ids_to_display' => $itemIdsToDisplay]);
        $response->assertStatus(200);
        $response->assertValid(['user_id', 'item_ids_to_display.0']);
        $response->assertJsonStructure(['user_weather_forecast_item']);
        $this->assertSame(count($itemIdsToDisplay), count($response['user_weather_forecast_item']));
        $user->refresh();
        $itemIdsFromRelation = $user->weatherForecastItems->pluck('id')->all();
        $this->assertSame(count($itemIdsToDisplay), count($itemIdsFromRelation));
        Auth::logout();
    }

    /**
     * /api/settings/email_changeへのGETリクエスト
     */
    public function test_get_api_settings_email_change(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/settings/email_change');
        $response->assertStatus(200);
        $response->assertJsonStructure(['user']);
        $this->assertNotNull($response['user']);
        Auth::logout();
    }

    /**
     * /api/users/emailへのPOSTリクエスト1
     */
    public function test_post_api_users_email1(): void
    {
        DB::table('email_change_requests')->delete();
        DB::table('users')->where('email', '<>', config('const.test_user_email1'))->delete();
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // user_idがない
        $response = $this->actingAs($user)->post('/api/users/email', ['email' => config('const.test_user_email2')]);
        $response->assertStatus(400);
        $response->assertInvalid(['user_id']);
        $response->assertValid(['email']);
        Auth::logout();
    }

    /**
     * /api/users/emailへのPOSTリクエスト2
     */
    public function test_post_api_users_email2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // user_idがログイン中のユーザーと違う
        $response = $this->actingAs($user)->post('/api/users/email', ['user_id' => 99999999, 'email' => config('const.test_user_email2')]);
        $response->assertStatus(403);
        Auth::logout();
    }

    /**
     * /api/users/emailへのPOSTリクエスト3
     */
    public function test_post_api_users_email3(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $user->is_test_user = true;
        $user->save();
        Auth::login($user);
        // テストユーザーの場合
        $response = $this->actingAs($user)->post('/api/users/email', ['user_id' => 99999999, 'email' => config('const.test_user_email2')]);
        $response->assertStatus(403);
        Auth::logout();
        $user->is_test_user = false;
        $user->save();
    }

    /**
     * /api/users/emailへのPOSTリクエスト4
     */
    public function test_post_api_users_email4(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // emailがない
        $response = $this->actingAs($user)->post('/api/users/email', ['user_id' => $user->id]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['email']);
        Auth::logout();
    }

    /**
     * /api/users/emailへのPOSTリクエスト5
     */
    public function test_post_api_users_email5(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // emailがメールアドレスの形式ではない
        $response = $this->actingAs($user)->post('/api/users/email', ['user_id' => $user->id, 'email' => 'a']);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['email']);
        Auth::logout();
    }

    /**
     * /api/users/emailへのPOSTリクエスト6
     */
    public function test_post_api_users_email6(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // emailがusersテーブルに存在する
        $response = $this->actingAs($user)->post('/api/users/email', ['user_id' => $user->id, 'email' => $user->email]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['email']);
        Auth::logout();
    }

    /**
     * /api/users/emailへのPOSTリクエスト7
     */
    public function test_post_api_users_email7(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $newEmail = 'test@example.com';
        $response = $this->actingAs($user)->post('/api/users/email', ['user_id' => $user->id, 'email' => $newEmail]);
        $response->assertStatus(200);
        $response->assertValid(['user_id', 'email']);
        $response->assertJsonStructure(['status']);
        $this->assertSame(200, $response['status']);
        $this->assertNotNull(EmailChangeRequest::where('user_id', $user->id)->where('email', $newEmail)->first());
        Auth::logout();
    }

    /**
     * /api/users/emailへのPOSTリクエスト8（もう一度同じメールアドレスで変更リクエストを登録する）
     */
    public function test_post_api_users_email8(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $newEmail = 'test@example.com';
        $response = $this->actingAs($user)->post('/api/users/email', ['user_id' => $user->id, 'email' => $newEmail]);
        $response->assertStatus(200);
        $response->assertValid(['user_id', 'email']);
        $response->assertJsonStructure(['status']);
        $this->assertSame(200, $response['status']);
        $this->assertNotNull(EmailChangeRequest::where('user_id', $user->id)->where('email', $newEmail)->first());
        Auth::logout();
    }

    /**
     * /api/users/emailへのPOSTリクエスト9
     */
    public function test_post_api_users_email9(): void
    {
        $user = new User;
        $user->email = config('const.test_user_email2');
        $user->password = Hash::make('test1234');
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();
        Auth::login($user);
        $newEmail = 'test@example.com';
        // すでに別のユーザーが同じメールアドレスへの変更申請を行なっているのでエラーになるはず
        $response = $this->actingAs($user)->post('/api/users/email', ['user_id' => $user->id, 'email' => $newEmail]);
        $response->assertStatus(400);
        $response->assertValid(['user_id']);
        $response->assertInvalid(['email']);
        Auth::logout();
    }

    /**
     * /api/settings/password_changeへのGETリクエスト
     */
    public function test_get_api_settings_password_change(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $response = $this->actingAs($user)->get('/api/settings/password_change');
        $response->assertStatus(200);
        $response->assertJsonStructure(['user']);
        $this->assertNotNull($response['user']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト1
     */
    public function test_patch_users_password1(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // user_idがない
        $password = 'test1234';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['password' => $password, 'new_password' => $newPassword]);
        $response->assertStatus(400);
        $response->assertInvalid(['user_id']);
        $response->assertValid(['password', 'new_password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト2
     */
    public function test_patch_users_password2(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // user_idがログインユーザーと違う
        $password = 'test1234';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => 99999999, 'password' => $password, 'new_password' => $newPassword]);
        $response->assertStatus(403);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト3
     */
    public function test_patch_users_password3(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        $user->is_test_user = true;
        $user->save();
        Auth::login($user);
        // テストユーザーのuser_id
        $password = 'test1234';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => 99999999, 'password' => $password, 'new_password' => $newPassword]);
        $response->assertStatus(403);
        Auth::logout();
        $user->is_test_user = false;
        $user->save();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト4
     */
    public function test_patch_users_password4(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // passwordがない
        $password = 'test1234';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'new_password' => $newPassword]);
        $response->assertStatus(400);
        $response->assertValid(['user_id', 'new_password']);
        $response->assertInvalid(['password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト5
     */
    public function test_patch_users_password5(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // passwordが短い
        $password = 'test123';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'new_password' => $newPassword]);
        $response->assertStatus(400);
        $response->assertValid(['user_id', 'new_password']);
        $response->assertInvalid(['password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト6
     */
    public function test_patch_users_password6(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // passwordが長い
        $password = 'test123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'new_password' => $newPassword]);
        $response->assertStatus(400);
        $response->assertValid(['user_id', 'new_password']);
        $response->assertInvalid(['password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト7
     */
    public function test_patch_users_password7(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // passwordにハイフンが含まれている
        $password = 'test-1234';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'new_password' => $newPassword]);
        $response->assertStatus(400);
        $response->assertValid(['user_id', 'new_password']);
        $response->assertInvalid(['password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト8
     */
    public function test_patch_users_password8(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // new_passwordがない
        $password = 'test1234';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'password' => $password]);
        $response->assertStatus(400);
        $response->assertValid(['user_id', 'password']);
        $response->assertInvalid(['new_password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト9
     */
    public function test_patch_users_password9(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // new_passwordが短い
        $password = 'test1234';
        $newPassword = 'test567';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'password' => $password, 'new_password' => $newPassword]);
        $response->assertStatus(400);
        $response->assertValid(['user_id', 'password']);
        $response->assertInvalid(['new_password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト10
     */
    public function test_patch_users_password10(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // new_passwordが長い
        $password = 'test5678';
        $newPassword = 'test123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'password' => $password, 'new_password' => $newPassword]);
        $response->assertStatus(400);
        $response->assertValid(['user_id', 'password']);
        $response->assertInvalid(['new_password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト11
     */
    public function test_patch_users_password11(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        // new_passwordにハイフンが含まれている
        $password = 'test1234';
        $newPassword = 'test-5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'password' => $password, 'new_password' => $newPassword]);
        $response->assertStatus(400);
        $response->assertValid(['user_id', 'password']);
        $response->assertInvalid(['new_password']);
        Auth::logout();
    }

    /**
     * /api/users/passwordへのPATCHリクエスト12
     */
    public function test_patch_users_password12(): void
    {
        $user = User::where('email', config('const.test_user_email1'))->first();
        Auth::login($user);
        $password = 'test1234';
        $newPassword = 'test5678';
        $response = $this->actingAs($user)->patch('/api/users/password', ['user_id' => $user->id, 'password' => $password, 'new_password' => $newPassword]);
        $response->assertStatus(200);
        $response->assertValid(['user_id', 'password', 'new_password']);
        $response->assertJsonStructure(['status']);
        $this->assertSame(200, $response['status']);
        $user->refresh();
        $this->assertTrue(password_verify($newPassword, $user->password));
        Auth::logout();
    }
}
