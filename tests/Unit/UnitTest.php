<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Symfony\Component\HttpKernel\Exception\HttpException;

use App\Services\WeatherForecastService;
use App\Services\AreaGroupService;
use App\Services\EmailChangeService;
use App\Services\PasswordResetService;
use App\Services\UserService;
use App\Services\UserWeatherForecastItemService;
use App\Services\WeatherForecastItemService;

use App\Models\User;
use App\Models\UserWeatherForecastItem;
use App\Models\WeatherForecastItem;
use App\Models\Area;
use App\Models\AreaGroup;
use App\Models\EmailChangeRequest;
use App\Models\PasswordResetRequest;
use App\Models\UserRegisterToken;

class UnitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // 外部キー制約を無効にする
        User::truncate();
        UserWeatherForecastItem::truncate();
        EmailChangeRequest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // 外部キー制約を有効にする
    }

    /**
     * WeatherForecastServiceの各メソッドの単体テスト
     */
    public function test_weather_forecast_service(): void
    {
        $weatherForecastService = app()->make(WeatherForecastService::class);

        // convertWindDegToTextのテスト

        $this->assertSame('北', $weatherForecastService->convertWindDegToText(0));
        $this->assertSame('北', $weatherForecastService->convertWindDegToText(11));
        $this->assertSame('北北東', $weatherForecastService->convertWindDegToText(12));
        $this->assertSame('北北東', $weatherForecastService->convertWindDegToText(33));
        $this->assertSame('北東', $weatherForecastService->convertWindDegToText(34));
        $this->assertSame('北東', $weatherForecastService->convertWindDegToText(56));
        $this->assertSame('東北東', $weatherForecastService->convertWindDegToText(57));
        $this->assertSame('東北東', $weatherForecastService->convertWindDegToText(78));
        $this->assertSame('東', $weatherForecastService->convertWindDegToText(79));
        $this->assertSame('東', $weatherForecastService->convertWindDegToText(101));
        $this->assertSame('東南東', $weatherForecastService->convertWindDegToText(102));
        $this->assertSame('東南東', $weatherForecastService->convertWindDegToText(123));
        $this->assertSame('南東', $weatherForecastService->convertWindDegToText(124));
        $this->assertSame('南東', $weatherForecastService->convertWindDegToText(146));
        $this->assertSame('南南東', $weatherForecastService->convertWindDegToText(147));
        $this->assertSame('南南東', $weatherForecastService->convertWindDegToText(168));
        $this->assertSame('南', $weatherForecastService->convertWindDegToText(169));
        $this->assertSame('南', $weatherForecastService->convertWindDegToText(191));
        $this->assertSame('南南西', $weatherForecastService->convertWindDegToText(192));
        $this->assertSame('南南西', $weatherForecastService->convertWindDegToText(213));
        $this->assertSame('南西', $weatherForecastService->convertWindDegToText(214));
        $this->assertSame('南西', $weatherForecastService->convertWindDegToText(236));
        $this->assertSame('西南西', $weatherForecastService->convertWindDegToText(237));
        $this->assertSame('西南西', $weatherForecastService->convertWindDegToText(258));
        $this->assertSame('西', $weatherForecastService->convertWindDegToText(259));
        $this->assertSame('西', $weatherForecastService->convertWindDegToText(281));
        $this->assertSame('西北西', $weatherForecastService->convertWindDegToText(282));
        $this->assertSame('西北西', $weatherForecastService->convertWindDegToText(303));
        $this->assertSame('北西', $weatherForecastService->convertWindDegToText(304));
        $this->assertSame('北西', $weatherForecastService->convertWindDegToText(326));
        $this->assertSame('北北西', $weatherForecastService->convertWindDegToText(327));
        $this->assertSame('北北西', $weatherForecastService->convertWindDegToText(348));
        $this->assertSame('北', $weatherForecastService->convertWindDegToText(349));
        $this->assertSame('北', $weatherForecastService->convertWindDegToText(360));

        // getDayOfWeekTextのテスト

        $this->assertSame('', $weatherForecastService->getDayOfWeekText(-1));
        $this->assertSame('（日）', $weatherForecastService->getDayOfWeekText(0));
        $this->assertSame('（月）', $weatherForecastService->getDayOfWeekText(1));
        $this->assertSame('（火）', $weatherForecastService->getDayOfWeekText(2));
        $this->assertSame('（水）', $weatherForecastService->getDayOfWeekText(3));
        $this->assertSame('（木）', $weatherForecastService->getDayOfWeekText(4));
        $this->assertSame('（金）', $weatherForecastService->getDayOfWeekText(5));
        $this->assertSame('（土）', $weatherForecastService->getDayOfWeekText(6));
        $this->assertSame('', $weatherForecastService->getDayOfWeekText(7));

        // getThreeHourForecastDataFromCacheとmakeRequestToThreeHourForecastApiのテスト

        Cache::flush(); // キャッシュ全体をクリア
        $area = Area::orderBy('id', 'asc')->first();
        // キャッシュに保存されていない場合はNULLが返ってくることを確認
        $this->assertNull($weatherForecastService->getThreeHourForecastDataFromCache($area->id));
        // リクエスト回数の制限を１分間に１回に変更する
        Config::set('const.weather_api.three_hour_forecast.max_requests_per_minute', 1);
        $apiResponse = $weatherForecastService->makeRequestToThreeHourForecastApi($area->id, $area->latitude, $area->longitude);
        $this->assertIsArray($apiResponse);
        // キャッシュに保存されている場合は配列が返ってくることを確認
        $this->assertIsArray($weatherForecastService->getThreeHourForecastDataFromCache($area->id));

        // １分間に１回のリクエスト回数制限を超えた場合にステータスコード429のHttpExceptionがスローされることを確認
        try {
            $result = $weatherForecastService->makeRequestToThreeHourForecastApi($area->id, $area->latitude, $area->longitude);
            $this->assertTrue(false);
        } catch (HttpException $e) {
            $this->assertSame(429, $e->getStatusCode());
        }

        // createThreeHourForecastDataForDisplayのテスト

        // リクエスト回数の制限を１分間に２０回に変更する
        Config::set('const.weather_api.three_hour_forecast.max_requests_per_minute', 20);
        // ３時間天気予報APIのエンドポイントを空のJSONを返すAPIに変える
        Config::set('const.weather_api.three_hour_forecast.endpoint', config('app.url') . '/api/test');
        // APIが空のJSONを返した場合はfalseを返すことを確認
        $this->assertFalse($weatherForecastService->makeRequestToThreeHourForecastApi($area->id, $area->latitude, $area->longitude));

        // テストのためのユーザーを作成する
        try {
            DB::beginTransaction();
            $user = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
            $itemIds = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop', 'rain_3h', 'humidity', 'wind'])->orderBy('display_order', 'asc')->get()->pluck('id')->toArray();
            $displayOrder = 0;

            foreach ($itemIds as $itemId) {
                $displayOrder++;
                UserWeatherForecastItem::create(['user_id' => $user->id, 'weather_forecast_item_id' => $itemId, 'display_order' => $displayOrder]);
            }

            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            $this->assertTrue(false);
        }

        // 時刻、天気アイコン、気温、降水確率、降水量、湿度、風向風速の順になっていることを確認する

        $dataForDisplay = $weatherForecastService->createThreeHourForecastDataForDisplay($user, $apiResponse);
        $this->assertIsArray($dataForDisplay);

        foreach ($dataForDisplay as $dateKey => $datum) {
            $this->assertTrue(mb_strlen($dateKey) === 8);
            $this->assertTrue(ctype_digit($dateKey));
            $this->assertArrayHasKey('date_text', $datum);
            $this->assertArrayHasKey('headers', $datum);
            $this->assertArrayHasKey('value_list', $datum);
            
            if (isset($datum['value_list'])) {
                foreach ($datum['value_list'] as $values) {
                    $this->assertCount(7, $values);
                    $this->assertIsString($values[0]);
                    $this->assertTrue(in_array($values[0], ['00', '03', '06', '09', '12', '15', '18', '21'], true));
                    $this->assertIsString($values[1]);
                    $this->assertTrue(Str::startsWith($values[1], '<img class="p-wheather-forecast__table-td-icon" src="https://openweathermap.org/img/wn/'));
                    $this->assertIsString($values[2]);
                    $this->assertTrue(Str::endsWith($values[2], '℃'));
                    $this->assertIsString($values[3]);
                    $this->assertTrue(Str::endsWith($values[3], '%'));
                    $this->assertIsString($values[4]);
                    $this->assertTrue(Str::endsWith($values[4], 'mm/3h'));
                    $this->assertIsString($values[5]);
                    $this->assertTrue(Str::endsWith($values[5], '%'));
                    $this->assertIsString($values[6]);
                    $this->assertTrue(Str::contains($values[6], 'm/s<br>'));
                }
            }
        }
    }

    /**
     * AreaGroupServiceの各メソッドのテスト
     */
    public function test_area_group_service(): void
    {
        // getAreaGroupAndChildrenのテスト

        $areaGroupService = app()->make(AreaGroupService::class);

        // キャッシュからデータが取得されるかテストする

        $cacheKey = config('const.area_group_and_children_cache_key') . 'null';
        $data = ['area_group_and_children_test_' . Str::uuid()];
        Cache::put($cacheKey, $data, 3600);
        $dataFromCache = $areaGroupService->getAreaGroupAndChildren(null);
        $this->assertSame($data, $dataFromCache);

        Cache::flush(); // キャッシュ全体をクリア

        // 引数がnullの場合（最上位の地域グループの場合）にid、name、parent_area_group_idがNULLであることを確認する

        $data = $areaGroupService->getAreaGroupAndChildren(null);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('parent_area_group_id', $data);
        $this->assertArrayHasKey('children', $data);
        $this->assertNull($data['id']);
        $this->assertNull($data['name']);
        $this->assertNull($data['parent_area_group_id']);

        // 引数が存在する地域グループIDの場合にid、name、parent_area_group_idがNULLではないことを確認する

        $areaGroup = AreaGroup::orderBy('id', 'asc')->first();
        $data = $areaGroupService->getAreaGroupAndChildren($areaGroup->id);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('parent_area_group_id', $data);
        $this->assertArrayHasKey('children', $data);
        $this->assertSame($areaGroup->id, $data['id']);
        $this->assertSame($areaGroup->name, $data['name']);
        $this->assertSame($areaGroup->parent_area_group_id, $data['parent_area_group_id']);

        // 引数が存在しない地域グループIDの場合はfalseを返すことを確認する

        $this->assertFalse($areaGroupService->getAreaGroupAndChildren(99999999));
    }

    /**
     * EmailChangeServiceの各メソッドの単体テスト
     */
    public function test_email_change_service(): void
    {
        $emailChangeService = app()->make(EmailChangeService::class);

        // createRequestのテスト
        
        // 登録されることを確認する
        $user1 = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $emailChangeRequest = $emailChangeService->createRequest($user1->id, config('const.test_user_email2'));
        $this->assertTrue($emailChangeRequest instanceof EmailChangeRequest);
        $this->assertSame($user1->id, $emailChangeRequest->user_id);
        $this->assertSame(config('const.test_user_email2'), $emailChangeRequest->email);
        $this->assertTrue(Str::startsWith($emailChangeRequest->token, dechex($user1->id) . '-'));
        $this->assertFalse($emailChangeService->createRequest(-1, config('const.test_user_email2')));
        $this->assertModelExists($emailChangeRequest);

        // changeUserEmailのテスト

        // ユーザーのメールアドレスが更新され、EmailChangeRequestが削除されることを確認する
        $user1 = $emailChangeService->changeUserEmail($user1, $emailChangeRequest->email, $emailChangeRequest->id);
        $this->assertSame($emailChangeRequest->email, $user1->email);
        $this->assertNull(EmailChangeRequest::find($emailChangeRequest->id));
        $user2 = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        // データベースのエラー（一意制約違反）が発生した場合はfalseを返すことを確認する
        $this->assertFalse($emailChangeService->changeUserEmail($user2, config('const.test_user_email2'), $emailChangeRequest->id));
    }

    /**
     * Commonの各メソッドの単体テスト
     */
    public function test_common(): void
    {
        $uuid = \Common::generateConfirmationUrlToken(1);
        $this->assertIsString($uuid);
        $this->assertTrue(Str::startsWith($uuid, '1-'));
        $this->assertTrue(!!preg_match('/\A[0-9a-z\-]+\z/', $uuid));
        $uuid = \Common::generateConfirmationUrlToken(9);
        $this->assertIsString($uuid);
        $this->assertTrue(Str::startsWith($uuid, '9-'));
        $this->assertTrue(!!preg_match('/\A[0-9a-z\-]+\z/', $uuid));
        $uuid = \Common::generateConfirmationUrlToken(10);
        $this->assertIsString($uuid);
        $this->assertTrue(Str::startsWith($uuid, 'a-'));
        $this->assertTrue(!!preg_match('/\A[0-9a-z\-]+\z/', $uuid));
        $uuid = \Common::generateConfirmationUrlToken(15);
        $this->assertIsString($uuid);
        $this->assertTrue(Str::startsWith($uuid, 'f-'));
        $this->assertTrue(!!preg_match('/\A[0-9a-z\-]+\z/', $uuid));
        $uuid = \Common::generateConfirmationUrlToken(16);
        $this->assertIsString($uuid);
        $this->assertTrue(Str::startsWith($uuid, '10-'));
        $this->assertTrue(!!preg_match('/\A[0-9a-z\-]+\z/', $uuid));
    }

    /**
     * PasswordResetServiceの各メソッドの単体テスト
     */
    public function test_password_reset_service(): void
    {
        $passwordResetService = app()->make(PasswordResetService::class);

        // createRequestのテスト

        // 登録されることを確認する
        $user = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $passwordResetRequest = $passwordResetService->createRequest($user->id, $user->email);
        $this->assertTrue($passwordResetRequest instanceof PasswordResetRequest);
        $this->assertSame($user->id, $passwordResetRequest->user_id);
        $this->assertTrue(Str::startsWith($passwordResetRequest->token, dechex($user->id) . '-'));
        $this->assertModelExists($passwordResetRequest);
        // データベースのエラー（unsigned integerのカラムに-1を登録しようとする）が発生した場合はfalseを返すことを確認する
        $this->assertFalse($passwordResetService->createRequest(-1, config('const.test_user_email1')));

        // resetのテスト

        // パスワードが変更され、PasswordResetRequestが削除されることを確認する
        $newPassword = 'newpassword';
        $user = $passwordResetService->reset($user, $newPassword, $passwordResetRequest->id);
        $this->assertTrue($user instanceof User);
        $this->assertTrue(password_verify($newPassword, $user->password));
        $this->assertNull(PasswordResetRequest::find($passwordResetRequest->id));
    }

    /**
     * UserServiceの各メソッドの単体テスト
     */
    public function test_user_service(): void
    {
        $userService = app()->make(UserService::class);

        // registerのテスト

        // UserとUserRegisterTokenが登録されることを確認する
        $password = 'test1234';
        $user = $userService->register(config('const.test_user_email1'), $password);
        $this->assertTrue($user instanceof User);
        $this->assertSame(config('const.test_user_email1'), $user->email);
        $this->assertTrue(password_verify($password, $user->password));
        $this->assertModelExists($user);
        $userRegisterToken = UserRegisterToken::where('user_id', $user->id)->first();
        $this->assertNotNull($userRegisterToken);
        $this->assertTrue(Str::startsWith($userRegisterToken->token, dechex($user->id) . '-'));

        // completeRegistrationのテスト
        
        // email_verified_atが更新され、天気、気温、降水確率が表示設定になり、UserRegisterTokenが削除されることを確認する
        $user = $userService->completeRegistration($user, $userRegisterToken->id);
        $this->assertTrue($user instanceof User);
        $weatherForecastItems = $user->weatherForecastItems;
        $this->assertSame(3, $weatherForecastItems->count());
        $weather = $weatherForecastItems->where('name', 'weather')->first();
        $this->assertNotNull($weather->id);
        $temp = $weatherForecastItems->where('name', 'temp')->first();
        $this->assertNotNull($temp->id);
        $pop = $weatherForecastItems->where('name', 'pop')->first();
        $this->assertNotNull($pop->id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull(UserRegisterToken::find($userRegisterToken->id));

        // getUserStoreStateのテスト

        // 地域が設定されていない場合はarea_nameがnullになることを確認する
        $result = $userService->getUserStoreState($user);
        $this->assertIsArray($result);
        $this->assertSame($user->id, $result['id']);
        $this->assertSame($user->email, $result['email']);
        $this->assertSame($user->is_test_user, $result['is_test_user']);
        $this->assertSame($user->area_id, $result['area_id']);
        $this->assertNull($result['area_name']);
        // 地域が設定されていない場合はarea_nameがnullになることを確認する
        $area1 = Area::orderBy('id', 'asc')->first(); // ID昇順で1件目の地域を取得
        $user->area_id = $area1->id;
        $user->save();
        $result = $userService->getUserStoreState($user);
        $this->assertIsArray($result);
        $this->assertSame($user->id, $result['id']);
        $this->assertSame($user->email, $result['email']);
        $this->assertSame($user->is_test_user, $result['is_test_user']);
        $this->assertSame($user->area_id, $result['area_id']);
        $this->assertSame($area1->name, $result['area_name']);

        // updateAreaIdのテスト

        // users.area_idが更新されることを確認する
        $area2 = Area::where('id', '<>', $area1->id)->orderBy('id', 'asc')->first(); // 別の地域を取得
        $user = $userService->updateAreaId($user, $area2->id);
        $this->assertTrue($user instanceof User);
        $this->assertSame($area2->id, $user->area_id);

        // updatePasswordのテスト

        // users.passwordが更新されることを確認する
        $newPassword = 'newpassword';
        $user = $userService->updatePassword($user, $newPassword);
        $this->assertTrue($user instanceof User);
        $this->assertTrue(password_verify($newPassword, $user->password));

        // unregisterのテスト

        // usersテーブルからレコードが削除されることを確認する
        $this->assertTrue($userService->unregister($user->id, $user->email));
        $this->assertNull(User::find($user->id));
    }

    /**
     * UserWeatherForecastItemServiceの各メソッドの単体テスト
     */
    public function test_user_weather_forecast_item_service(): void
    {
        $userWeatherForecastItemService = app()->make(UserWeatherForecastItemService::class);

        // updateWeatherForecastItemsToDisplayのテスト

        // ユーザーを作成し、天気、天気、降水確率を表示設定にする
        $user = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $itemIds = WeatherForecastItem::whereIn('name', ['weather', 'temp', 'pop'])->orderBy('display_order', 'asc')->get()->pluck('id')->toArray();
        $displayOrder = 0;

        foreach ($itemIds as $itemId) {
            $displayOrder++;
            UserWeatherForecastItem::create(['user_id' => $user->id, 'weather_forecast_item_id' => $itemId, 'display_order' => $displayOrder]);
        }

        // 更新処理後に、降水量、湿度、風向風速が表示設定になることを確認する
        $newItemIds = WeatherForecastItem::whereIn('name', ['rain_3h', 'humidity', 'wind'])->orderBy('id', 'desc')->get()->pluck('id')->all();
        $expected = ['user_weather_forecast_item' => []];
        $displayOrder = 0;

        foreach ($newItemIds as $itemId) {
            $displayOrder++;
            $expected['user_weather_forecast_item'][] = [
                'user_id' => $user->id,
                'weather_forecast_item_id' => $itemId,
                'display_order' => $displayOrder,
            ];
        }

        // 戻り値のチェック
        $result = $userWeatherForecastItemService->updateWeatherForecastItemsToDisplay($user->id, $newItemIds);
        $this->assertSame($expected, $result);
        // データベースから取得してもう一度チェック
        $userWeatherForecastItems = UserWeatherForecastItem::where('user_id', $user->id)->orderBy('display_order', 'asc')->get()->toArray();
        $this->assertSame($expected, ['user_weather_forecast_item' => $userWeatherForecastItems]);
    }

    /**
     * WeatherForecastItemServiceの各メソッドの単体テスト
     */
    public function test_weather_forecast_item_service(): void
    {
        $weatherForecastItemService = app()->make(WeatherForecastItemService::class);

        // 表示設定にする項目（天気、気温）
        $itemsToDisplay = WeatherForecastItem::select('id', 'name', 'display_name')->whereIn('name', ['weather', 'temp'])->orderBy('display_order', 'asc')->get()->toArray();
        // 非表示設定にする項目（天気、気温以外）
        $itemsNotToDisplay = WeatherForecastItem::select('id', 'name', 'display_name')->whereNotIn('name', ['weather', 'temp'])->orderBy('display_order', 'asc')->get()->toArray();
        // すべての項目
        $allItems = WeatherForecastItem::select('id', 'name', 'display_name')->orderBy('display_order', 'asc')->get()->toArray();
        $user = User::create(['email' => config('const.test_user_email1'), 'password' => Hash::make('test1234')]);
        $displayOrder = 0;

        foreach ($itemsToDisplay as $item) {
            $displayOrder++;
            UserWeatherForecastItem::create(['user_id' => $user->id, 'weather_forecast_item_id' => $item['id'], 'display_order' => $displayOrder]);
        }

        // 表示する項目のチェック
        $items = $weatherForecastItemService->getWeatherForecastItemsToDisplayByUser($user);
        $this->assertSame($itemsToDisplay, $items);
        // 表示しない項目のチェック
        $items = $weatherForecastItemService->getWeatherForecastItemsToHideByUser($user);
        $this->assertSame($itemsNotToDisplay, $items);
        // 中間テーブルからこのユーザーと項目の紐付けを全て削除する
        DB::table('user_weather_forecast_item')->where('user_id', $user->id)->delete();
        $user->refresh();
        // 全ての項目が表示しない項目として取得されることを確認する
        $items = $weatherForecastItemService->getWeatherForecastItemsToHideByUser($user);
        $this->assertSame($allItems, $items);
    }
}
