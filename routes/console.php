<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('delete-members-without-email-authentication', function () {
    $twoDaysAgo = Carbon::parse('-2 days')->format('Y-m-d H:i:s');
    // 登録されてからメールアドレスの確認が行われずに２日以上経過した会員の情報は削除する
    DB::table('users')
        ->whereNull('email_verified_at')
        ->where('created_at', '<=', $twoDaysAgo)
        ->delete();
})->purpose('Delete members without email authentication');

Artisan::command('delete-expired-user-register-tokens', function () {
    $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
    // 有効期限から１日以上経過した新規会員登録時のトークンを削除する
    DB::table('user_register_tokens')
        ->where('expires_at', '<=', $oneDayAgo)
        ->delete();
})->purpose('Delete expired user register tokens');

Artisan::command('delete-expired-password-reset-requests', function () {
    $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
    // 有効期限から１日以上経過したパスワード変更申請を削除する
    DB::table('password_reset_requests')
        ->where('expires_at', '<=', $oneDayAgo)
        ->delete();
})->purpose('Delete expired password reset requests');

Artisan::command('delete-expired-email-change-requests', function () {
    $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
    // 有効期限から１日以上経過したメールアドレス変更申請を削除する
    DB::table('email_change_requests')
        ->where('expires_at', '<=', $oneDayAgo)
        ->delete();
})->purpose('Delete expired email change requests');
