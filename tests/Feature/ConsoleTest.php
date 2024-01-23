<?php

namespace Tests\Feature;

use Tests\TestCase;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\EmailChangeRequest;
use App\Models\PasswordResetRequest;
use App\Models\UserRegisterToken;

class ConsoleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // 外部キー制約を無効にする
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // 外部キー制約を有効にする
    }

    /**
     * delete_members_without_email_authenticationコマンドのテスト
     */
    public function test_command_delete_members_without_email_authentication(): void
    {
        // メール未認証のユーザーが49時間前と47時間前に登録されたことにする
        $createdAt = Carbon::parse('-49 hours')->format('Y-m-d H:i:s');
        $user1 = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $user1->created_at = $createdAt;
        $user1->save();
        $createdAt = Carbon::parse('-47 hours')->format('Y-m-d H:i:s');
        $user2 = User::create(['email' => config('const.test_user_email2'), 'password' => Hash::make('test1234')]);
        $user2->created_at = $createdAt;
        $user2->save();
        $this->artisan('delete-members-without-email-authentication')->assertSuccessful();
        // 49時間前に登録されたメールみ認証ユーザーが削除されることを確認する
        $this->assertNull(User::find($user1->id));
        // 47時間前に登録されたメールみ認証ユーザーが削除されていないことを確認する
        $user2 = User::find($user2->id);
        $this->assertNotNull($user2);
        $this->assertTrue($user2 instanceof User);
        $this->assertNotNull($user2->id);
        DB::table('users')->delete(); // usersテーブルのレコードを全件削除する
        // メール認証済みのユーザーが49時間前に登録されたことにする
        $createdAt = Carbon::parse('-49 hours')->format('Y-m-d H:i:s');
        $user3 = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $user3->created_at = $createdAt;
        $user3->email_verified_at = date('Y-m-d H:i:s');
        $user3->save();
        $this->artisan('delete-members-without-email-authentication')->assertSuccessful();
        // ユーザーが削除されないことを確認する
        $user3 = User::find($user3->id);
        $this->assertNotNull($user3);
        $this->assertTrue($user3 instanceof User);
        $this->assertNotNull($user3->id);
    }

    public function test_command_delete_expired_user_register_tokens(): void
    {
        $user1 = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $token = \Common::generateConfirmationUrlToken($user1->id);
        $expiresAt = new Carbon('-25 hours');
        $userRegisterToken1 = UserRegisterToken::create(['user_id' => $user1->id, 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $user2 = User::create(['email' => config('const.test_user_email2'), 'password' => Hash::make('test1234')]);
        $token = \Common::generateConfirmationUrlToken($user2->id);
        $expiresAt = new Carbon('-23 hours');
        $userRegisterToken2 = UserRegisterToken::create(['user_id' => $user2->id, 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $this->artisan('delete-expired-user-register-tokens')->assertSuccessful();
        // 有効期限が約25時間前のUserRegisterTokenが削除されることを確認する
        $this->assertNull(UserRegisterToken::find($userRegisterToken1->id));
        // 有効期限が約23時間前のUserRegisterTokenが削除されないことを確認する
        $userRegisterToken2 = UserRegisterToken::find($userRegisterToken2->id);
        $this->assertNotNull($userRegisterToken2);
        $this->assertTrue($userRegisterToken2 instanceof UserRegisterToken);
        $this->assertNotNull($userRegisterToken2->id);
    }

    public function test_command_delete_expired_password_reset_requests(): void
    {
        $user1 = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $token = \Common::generateConfirmationUrlToken($user1->id);
        $expiresAt = new Carbon('-25 hours');
        $passwordResetRequest1 = PasswordResetRequest::create(['user_id' => $user1->id, 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $user2 = User::create(['email' => config('const.test_user_email2'), 'password' => Hash::make('test1234')]);
        $token = \Common::generateConfirmationUrlToken($user2->id);
        $expiresAt = new Carbon('-23 hours');
        $passwordResetRequest2 = PasswordResetRequest::create(['user_id' => $user2->id, 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $this->artisan('delete-expired-password-reset-requests')->assertSuccessful();
        // 有効期限が約25時間前のPasswordResetRequestが削除されることを確認する
        $this->assertNull(PasswordResetRequest::find($passwordResetRequest1->id));
        // 有効期限が約25時間前のPasswordResetRequestが削除されないことを確認する
        $passwordResetRequest2 = PasswordResetRequest::find($passwordResetRequest2->id);
        $this->assertNotNull($passwordResetRequest2);
        $this->assertTrue($passwordResetRequest2 instanceof PasswordResetRequest);
        $this->assertNotNull($passwordResetRequest2->id);
    }

    public function test_command_delete_expired_email_change_requests(): void
    {
        $user = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $token = \Common::generateConfirmationUrlToken($user->id);
        $expiresAt = new Carbon('-25 hours');
        $emailChangeRequest = EmailChangeRequest::create(['user_id' => $user->id, 'email' => config('const.test_user_email2'), 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $this->artisan('delete-expired-email-change-requests')->assertSuccessful();
        // 有効期限が約25時間前のEmailChangeRequestが削除されることを確認する
        $this->assertNull(EmailChangeRequest::find($emailChangeRequest->id));
        DB::table('users')->delete(); // usersテーブルのレコードを全件削除する
        DB::table('email_change_requests')->delete(); // email_change_requestsテーブルのレコードを全件削除する
        $user = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $token = \Common::generateConfirmationUrlToken($user->id);
        $expiresAt = new Carbon('-23 hours');
        $emailChangeRequest = EmailChangeRequest::create(['user_id' => $user->id, 'email' => config('const.test_user_email2'), 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        $this->artisan('delete-expired-email-change-requests')->assertSuccessful();
        // 有効期限が約25時間前のEmailChangeRequestが削除されないことを確認する
        $emailChangeRequest = EmailChangeRequest::find($emailChangeRequest->id);
        $this->assertNotNull($emailChangeRequest);
        $this->assertTrue($emailChangeRequest instanceof EmailChangeRequest);
        $this->assertNotNull($emailChangeRequest->id);
    }
}
