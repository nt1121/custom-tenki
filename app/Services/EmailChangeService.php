<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\EmailChangeRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EmailChangeService
{
    /**
     * メールアドレス変更申請を作成する
     *
     * @param  int $userId 会員ID
     * @param  string $email 新しいメールアドレス
     * @return App\Models\EmailChangeRequest|bool
     */
    public function createRequest(int $userId, string $email): EmailChangeRequest | bool
    {
        try {
            DB::beginTransaction();
            $token = \Common::generateConfirmationUrlToken($userId);
            $expiresAt = new Carbon('+24 hours');
            $emailChangeRequest = EmailChangeRequest::updateOrCreate(['user_id' => $userId], ['email' => $email, 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
            $url = config('app.url') . '/email_change/' . $token;
            $text = <<<END
メールアドレスの変更画面よりメールアドレス変更の申請を受付けました。

24時間以内に下記のURLをクリックすることにより、メールアドレスの変更が完了します。
{$url}

※URLが有効期限切れの場合はもう一度メールアドレスの変更画面から申請をお願いいたします。
※このメールの送信後にメールアドレスの変更画面からのメールアドレス変更の申請が行われた場合、上記のURLは無効になります。
※CustomTenkiに会員登録をした覚えがない場合は、お手数ですがこのメールを破棄くださいますようお願い申し上げます。
END;
            Mail::to($email)->send(new NotificationMail('【CustomTenki】メールアドレスの確認', $text));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        return $emailChangeRequest;
    }

    /**
     * 会員のメールアドレスを変更する
     *
     * @param  App\Models\User $user
     * @param  string $email 新しいメールアドレス
     * @param  int $emailChangeRequestId 削除するメールアドレス変更申請のID
     * @return App\Models\User|bool
     */
    public function changeUserEmail(User $user, string $email, int $emailChangeRequestId): User | bool
    {
        try {
            DB::beginTransaction();
            $user->email = $email;
            $user->save();
            EmailChangeRequest::destroy($emailChangeRequestId);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        return $user;
    }
}
