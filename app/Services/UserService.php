<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\User;
use App\Models\UserRegisterToken;
use App\Models\UserWeatherForecastItem;
use App\Models\WeatherForecastItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService
{
    /**
     * 会員登録する（メールアドレスは未認証でまだログインはできない）
     *
     * @param  string $email 登録する会員のメールアドレス
     * @param  string $password 登録する会員のパスワード
     * @return bool
     */
    public function register(string $email, string $password)
    {
        try {
            DB::beginTransaction();
            $user = User::create(['email' => $email, 'password' => Hash::make($password)]);
            $token = \Common::generateConfirmationUrlToken($user->id);
            $expiresAt = new Carbon('+24 hours');
            UserRegisterToken::create(['user_id' => $user->id, 'token' => $token, 'expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
            $url = config('app.url') . '/register/' . $token;
            $text = <<<END
CustomTenkiにご登録いただき、ありがとうございます。

24時間以内に下記のURLをクリックして会員登録を完了してください。
{$url}

※URLが有効期限切れの場合はもう一度新規会員登録画面から登録をお願いいたします。
※有効期限内に上記のURLへのアクセスがない場合、一定時間後に会員情報は削除されます。
※CustomTenkiに会員登録をした覚えがない場合は、お手数ですがこのメールを破棄くださいますようお願い申し上げます。
END;
            Mail::to($user->email)->send(new NotificationMail('【CustomTenki】メールアドレスの確認', $text));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * メールアドレスの認証を行い、会員登録を完了する
     *
     * @param  App\Models\User $user
     * @param  int $userRegisterTokenId 削除する会員登録用トークンのID
     * @return bool
     */
    public function completeRegistration(User $user, int $userRegisterTokenId)
    {
        try {
            DB::beginTransaction();
            $itemIds = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('display_order', 'asc')->get()->pluck('id')->toArray();
            $displayOrder = 0;

            foreach ($itemIds as $itemId) {
                $displayOrder++;
                UserWeatherForecastItem::create(['user_id' => $user->id, 'weather_forecast_item_id' => $itemId, 'display_order' => $displayOrder]);
            }

            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->save();
            UserRegisterToken::destroy($userRegisterTokenId);
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        Auth::login($user);
        request()->session()->regenerate();
        return true;
    }

    /**
     * Vuexのストア用のログイン中の会員の連想配列を返す
     *
     * @param  App\Models\User $user
     * @return array
     */
    public function getUserStoreState(User $user)
    {
        $area = null;

        if (!is_null($user->area_id)) {
            $area = $user->area;
        }

        return [
            'id' => $user->id,
            'email' => $user->email,
            'is_test_user' => $user->is_test_user,
            'area_id' => $user->area_id,
            'area_name' => $area ? $area->name : null,
        ];
    }

    /**
     * 会員のエリアIDを更新する
     *
     * @param  App\Models\User $user
     * @param  int $areaId 新しいエリアID
     * @return App\Models\User
     */
    public function updateAreaId(User $user, int $areaId)
    {
        $user->area_id = $areaId;
        $user->save();
        return $user;
    }

    /**
     * 会員のパスワードを更新する
     *
     * @param  App\Models\User $user
     * @param  string $newPassword 新しいパスワード
     * @return App\Models\User
     */
    public function updatePassword(User $user, string $newPassword)
    {
        $user->password = Hash::make($newPassword);
        $user->save();
        return $user;
    }

    /**
     * アカウントを削除する
     *
     * @param  int $userId
     * @param  string $userEmail
     * @return void
     */
    public function unregister(int $userId, string $userEmail)
    {
        try {
            DB::beginTransaction();
            User::destroy($userId);
            $text = <<<END
アカウントの削除が完了いたしました。

※CustomTenkiに会員登録をした覚えがない場合は、お手数ですがこのメールを破棄くださいますようお願い申し上げます。
END;
            Mail::to($userEmail)->send(new NotificationMail('【CustomTenki】アカウントの削除', $text));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return true;
    }
}
