<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Tests\TestCase;

use App\Models\User;

class HttpTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        $this->seed(); // シーダーの実行
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // 外部キー制約を無効にする
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // 外部キー制約を有効にする
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // 外部キー制約を無効にする
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // 外部キー制約を有効にする
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
     * /loginへのGETリクエスト
     */
    public function test_get_login(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ログイン');
    }

    /**
     * /loginへのPOSTリクエスト1（バリデーションエラー）
     */
    public function test_post_login1(): void
    {
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
}
