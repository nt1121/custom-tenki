<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    /**
     * パスワード変更申請を作成する
     *
     * @param  int $userId 会員ID
     * @param  string $email 会員のメールアドレス
     * @return App\Models\PasswordResetRequest|bool
     */
    public function createRequest(int $userId, string $email): PasswordResetRequest | bool
    {
        try {
            DB::beginTransaction();
            $token = \Common::generateConfirmationUrlToken($userId);
            $expiresAt = new Carbon('+24 hours');
            $passwordResetRequest = PasswordResetRequest::updateOrCreate(['user_id' => $userId], ['token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
            $url = config('app.url') . '/password_reset/' . $token;
            $text = <<<END
パスワードの再設定画面よりパスワードの再設定の申請を受付けました。

24時間以内に下記のURLをクリックしてパスワードの再設定を行なってください。
{$url}

※URLが有効期限切れの場合はもう一度パスワード再設定画面から申請をお願いいたします。
※このメールの送信後にパスワード再設定画面からのパスワードの再設定の申請が行われた場合、上記のURLは無効になります。
※CustomTenkiに会員登録をした覚えがない場合は、お手数ですがこのメールを破棄くださいますようお願い申し上げます。
END;
            Mail::to($email)->send(new NotificationMail('【CustomTenki】パスワード再設定のご案内', $text));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        return $passwordResetRequest;
    }

    /**
     * 会員のパスワードを変更する
     *
     * @param  App\Models\User $user
     * @param  string $password 新しいパスワード
     * @param  int $passwordResetRequestId 削除するパスワード変更申請のID
     * @return App\Models\User|bool
     */
    public function reset(User $user, string $password, int $passwordResetRequestId): User | bool
    {
        try {
            DB::beginTransaction();
            $user->password = Hash::make($password);
            $user->save();
            PasswordResetRequest::destroy($passwordResetRequestId);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        return $user;
    }
}
