<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\WeatherForecastService;
use App\Services\AreaGroupService;
use Tests\CreatesApplication;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\UserWeatherForecastItem;
use App\Models\WeatherForecastItem;
use App\Models\Area;
use App\Models\AreaGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UnitTest extends TestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        UserWeatherForecastItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        UserWeatherForecastItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
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
        $area = Area::find(1);
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
            $user = User::create(['email' => config('const.system_admin_email_address'), 'password' => Hash::make('testtest')]);
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
        $areaGroupService = app()->make(AreaGroupService::class);

        // キャッシュからデータが取得されるかテストする

        // テストのためにキャッシュのキーを変更
        Config::set('const.area_group_and_children_cache_key', 'test_area_group_and_children_area_group_id_');
        $cacheKey = config('const.area_group_and_children_cache_key') . 'null';
        $data = ['area_group_and_children_test_' . Str::uuid()];
        Cache::put($cacheKey, $data, 3600);
        $dataFromCache = $areaGroupService->getAreaGroupAndChildren(null);
        $this->assertSame($data, $dataFromCache);

        Cache::flush(); // キャッシュ全体をクリア

        // 引数がnullの場合（最上位の地域グループの場合）

        $data = $areaGroupService->getAreaGroupAndChildren(null);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('parent_area_group_id', $data);
        $this->assertArrayHasKey('children', $data);
        $this->assertNull($data['id']);
        $this->assertNull($data['name']);
        $this->assertNull($data['parent_area_group_id']);

        // 引数が存在する地域グループIDの場合

        $areaGroup = AreaGroup::find(1);
        $data = $areaGroupService->getAreaGroupAndChildren($areaGroup->id);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('parent_area_group_id', $data);
        $this->assertArrayHasKey('children', $data);
        $this->assertSame($areaGroup->id, $data['id']);
        $this->assertSame($areaGroup->name, $data['name']);
        $this->assertSame($areaGroup->parent_area_group_id, $data['parent_area_group_id']);

        // 引数が存在しない地域グループIDの場合

        $this->assertFalse($areaGroupService->getAreaGroupAndChildren(99999999));
    }
}
