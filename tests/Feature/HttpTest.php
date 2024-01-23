<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

use Tests\TestCase;

use App\Models\User;
use App\Models\EmailChangeRequest;
use App\Models\UserRegisterToken;
use App\Models\PasswordResetRequest;

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
}
