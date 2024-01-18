<?php

namespace App\Console;

use App\Mail\NotificationMail;
use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('delete-members-without-email-authentication')
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

        $schedule->command('delete-expired-user-register-tokens')
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

        $schedule->command('delete-expired-password-reset-requests')
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

        $schedule->command('delete-expired-email-change-requests')
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
