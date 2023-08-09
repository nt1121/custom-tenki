<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->call(function () {
            $twoDaysAgo = Carbon::parse('-2 days')->format('Y-m-d H:i:s');
            // 登録されてからメールアドレスの確認が行われずに２日以上経過した会員の情報は削除する
            DB::table('users')
                ->whereNull('email_verified_at')
                ->where('created_at', '<=', $twoDaysAgo)
                ->delete();
        })->dailyAt('2:00');

        $schedule->call(function () {
            $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
            // 有効期限から１日以上経過した新規会員登録時のトークンを削除する
            DB::table('user_register_tokens')
                ->where('expires_at', '<=', $oneDayAgo)
                ->delete();
        })->dailyAt('2:15');

        $schedule->call(function () {
            $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
            // 有効期限から１日以上経過したパスワード変更申請を削除する
            DB::table('password_reset_requests')
                ->where('expires_at', '<=', $oneDayAgo)
                ->delete();
        })->dailyAt('2:30');

        $schedule->call(function () {
            $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
            // 有効期限から１日以上経過したメールアドレス変更申請を削除する
            DB::table('email_change_requests')
                ->where('expires_at', '<=', $oneDayAgo)
                ->delete();
        })->dailyAt('2:45');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
