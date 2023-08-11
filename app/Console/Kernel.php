<?php

namespace App\Console;

use App\Mail\NotificationMail;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
        })
            ->name('delete-members-without-email-authentication')
            ->dailyAt('2:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                // タスク失敗時
                // システム管理者のメールアドレスが設定されている場合はシステム管理者に通知メールを送信する
                if (config('const.system_admin_email_address')) {
                    $text = <<<END
メールアドレス未確認の会員レコードの削除バッチ処理が失敗しました。
調査をお願いいたします。
END;
                    Mail::to(config('const.system_admin_email_address'))->send(new NotificationMail('【CustomTenki】バッチ処理が失敗しました', $text));
                }
            });

        $schedule->call(function () {
            $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
            // 有効期限から１日以上経過した新規会員登録時のトークンを削除する
            DB::table('user_register_tokens')
                ->where('expires_at', '<=', $oneDayAgo)
                ->delete();
        })
            ->name('delete-expired-user-register-tokens')
            ->dailyAt('2:15')
            ->withoutOverlapping()
            ->onFailure(function () {
                // タスク失敗時
                // システム管理者のメールアドレスが設定されている場合はシステム管理者に通知メールを送信する
                if (config('const.system_admin_email_address')) {
                    $text = <<<END
有効期限切れの新規会員登録時のトークンレコードの削除バッチ処理が失敗しました。
調査をお願いいたします。
END;
                    Mail::to(config('const.system_admin_email_address'))->send(new NotificationMail('【CustomTenki】バッチ処理が失敗しました', $text));
                }
            });

        $schedule->call(function () {
            $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
            // 有効期限から１日以上経過したパスワード変更申請を削除する
            DB::table('password_reset_requests')
                ->where('expires_at', '<=', $oneDayAgo)
                ->delete();
        })
            ->name('delete-expired-password-reset-requests')
            ->dailyAt('2:30')
            ->withoutOverlapping()
            ->onFailure(function () {
                // タスク失敗時
                // システム管理者のメールアドレスが設定されている場合はシステム管理者に通知メールを送信する
                if (config('const.system_admin_email_address')) {
                    $text = <<<END
有効期限切れのパスワード変更申請レコードの削除バッチ処理が失敗しました。
調査をお願いいたします。
END;
                    Mail::to(config('const.system_admin_email_address'))->send(new NotificationMail('【CustomTenki】バッチ処理が失敗しました', $text));
                }
            });

        $schedule->call(function () {
            $oneDayAgo = Carbon::parse('-1 day')->format('Y-m-d H:i:s');
            // 有効期限から１日以上経過したメールアドレス変更申請を削除する
            DB::table('email_change_requests')
                ->where('expires_at', '<=', $oneDayAgo)
                ->delete();
        })
            ->name('delete-expired-email-change-requests')
            ->dailyAt('2:45')
            ->withoutOverlapping()
            ->onFailure(function () {
                // タスク失敗時
                // システム管理者のメールアドレスが設定されている場合はシステム管理者に通知メールを送信する
                if (config('const.system_admin_email_address')) {
                    $text = <<<END
有効期限切れのメールアドレス変更申請レコードの削除バッチ処理が失敗しました。
調査をお願いいたします。
END;
                    Mail::to(config('const.system_admin_email_address'))->send(new NotificationMail('【CustomTenki】バッチ処理が失敗しました', $text));
                }
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * スケジュールされたイベントで使用するデフォルトのタイムゾーン取得
     */
    protected function scheduleTimezone(): DateTimeZone | string | null
    {
        return 'Asia/Tokyo';
    }

}
