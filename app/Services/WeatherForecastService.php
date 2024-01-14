<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class WeatherForecastService
{
    /**
     * ３時間天気予報のデータをキャッシュから取得する
     * 
     * @param  int  $areaId 地域ID
     * @return array|null
     */
    public function getThreeHourForecastDataFromCache(int $areaId) {
        $cacheKey = config('const.weather_api.three_hour_forecast.cache_key') . $areaId;
        return Cache::get($cacheKey);
    }

    /**
     * ３時間天気予報のAPIにリクエストする
     * 
     * @param  int  $areaId 地域ID
     * @param  string $latitude 緯度
     * @param  string $longitude 経度
     * @return array|bool
     */
    public function makeRequestToThreeHourForecastApi(int $areaId, string $latitude, string $longitude) {
        // 一定時間内のリクエスト回数制限に達した場合はステータスコード429のエラーを返す
        if (RateLimiter::tooManyAttempts('three-hour-forecast-api-request', $perMinute = config('const.weather_api.three_hour_forecast.max_requests_per_minute'))) {
            abort(429);
        }

        RateLimiter::hit('three-hour-forecast-api-request');
        $url = config('const.weather_api.three_hour_forecast.endpoint') . '?lat=' . $latitude . '&lon=' . $longitude . '&units=metric&cnt=40&lang=ja&appid=' . config('const.weather_api.api_key');
        $data = json_decode(file_get_contents($url), true);

        // APIのレスポンスのバリデーション
        $validator = Validator::make($data, [
            'list' => 'required|array',
            'list.*.dt' => 'required|integer',
            'list.*.main.temp' => 'required|numeric',
            'list.*.main.humidity' => 'required|integer',
            'list.*.weather.0.icon' => 'required|string',
            'list.*.wind.speed' => 'required|numeric',
            'list.*.wind.deg' => 'required|integer|min:0|max:360',
            'list.*.pop' => 'required|numeric',
            'list.*.rain.3h' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            logger()->error('METHOD:' . __METHOD__ . ' LINE:' . __LINE__ . ' ３時間天気予報APIのレスポンスのバリデーションでエラーが発生しました。');
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                logger()->error($message);
            }
            return false;
        }

        // キャッシュに保存
        $cacheKey = config('const.weather_api.three_hour_forecast.cache_key') . $areaId;
        Cache::put($cacheKey, $data, 3600);
        return $data;
    }

    /**
     * 表示用の３時間天気予報のデータを作成する
     * 
     * @param  App\Models\User $user
     * @patam  array $data ３時間天気予報APIのレスポンスの配列
     * @return array
     */
    public function createThreeHourForecastDataForDisplay(User $user, array $data) {
        // APIのレスポンスを加工する
        $precessedData = [];

        foreach ($data['list'] as $datum) {
            $dt = Carbon::createFromTimestamp($datum['dt']);
            $precessedData[] = [
                'dt' => $datum['dt'],
                'date_key' => $dt->format('Ymd'),
                'date_text' => $dt->format('Y年m月d日') . $this->getDayOfWeekText($dt->dayOfWeek),
                'hour' => $dt->format('H'),
                'temp' => floor($datum['main']['temp']),
                'humidity' => $datum['main']['humidity'],
                'weather' => $datum['weather'][0]['icon'],
                'wind' => [
                    'speed' => $datum['wind']['speed'],
                    'direction' => $this->convertWindDegToText($datum['wind']['deg']),
                ],
                'pop' => floor($datum['pop'] * 100),
                'rain_3h' => $datum['rain']['3h'] ?? null,
            ];
        }

        // 表示する項目の設定順に並べる
        $result = [];
        // 4日後の0時0分0秒。この日時以降のデータは表示しない。
        $displayLimit = new Carbon('+4 days');
        $displayLimit->setTime(0, 0, 0);
        $itemsToDisplay = $user->weatherForecastItems;
        $headers = array_merge(['時刻'], $itemsToDisplay->pluck('display_name')->toArray());
        $itemNamesToDisplay = array_merge(['hour'], $itemsToDisplay->pluck('name')->toArray());
        $now = time();

        foreach ($precessedData as $datum) {
            // 現在時刻以前の場合は表示しない
            if ($datum['dt'] <= $now) {
                continue;
            }

            if ($datum['dt'] >= $displayLimit->timestamp) {
                break;
            }

            if (!isset($result[$datum['date_key']])) {
                $result[$datum['date_key']] = [
                    'date_text' => $datum['date_text'],
                    'headers' => $headers,
                    'value_list' => [],
                ];
            }

            $values = [];

            foreach ($itemNamesToDisplay as $itemName) {
                $value = $datum[$itemName];

                if ($itemName === 'hour') {
                    $values[] = $value;
                } elseif ($itemName === 'weather') {
                    $values[] = '<img class="p-wheather-forecast__table-td-icon" src="https://openweathermap.org/img/wn/' . $value . '.png" alt="天気">';
                } elseif ($itemName === 'temp') {
                    $values[] = $value . '℃';
                } elseif ($itemName === 'pop' || $itemName === 'humidity') {
                    $values[] = $value . '%';
                } elseif ($itemName === 'rain_3h') {
                    $values[] = is_null($value) ? '0mm/3h' : ($value . 'mm/3h');
                } elseif ($itemName === 'wind') {
                    $values[] = $value['speed'] . 'm/s<br>' . $value['direction'];
                } else {
                    // 不明な項目の場合は空欄
                    $values[] = '';
                }
            }

            $result[$datum['date_key']]['value_list'][] = $values;
        }

        return $result;
    }

    /**
     * 風向の角度を文字列の表現に変換する
     * 
     * @param  int $deg 風向（角度）
     * @return string
     */
    public function convertWindDegToText(int $deg): string
    {
        if ($deg < 11.25) {
            return '北';
        } elseif ($deg < 33.75) {
            return '北北東';
        } elseif ($deg < 56.25) {
            return '北東';
        } elseif ($deg < 78.75) {
            return '東北東';
        } elseif ($deg < 101.25) {
            return '東';
        } elseif ($deg < 123.75) {
            return '東南東';
        } elseif ($deg < 146.25) {
            return '南東';
        } elseif ($deg < 168.75) {
            return '南南東';
        } elseif ($deg < 191.25) {
            return '南';
        } elseif ($deg < 213.75) {
            return '南南西';
        } elseif ($deg < 236.25) {
            return '南西';
        } elseif ($deg < 258.75) {
            return '西南西';
        } elseif ($deg < 281.25) {
            return '西';
        } elseif ($deg < 303.75) {
            return '西北西';
        } elseif ($deg < 326.25) {
            return '北西';
        } elseif ($deg < 348.75) {
            return '北北西';
        } else {
            return '北';
        }
    }

    /**
     * 曜日の数値をかっこ付きの文字列の表現にして返す
     * 
     * @param  int $dayOfWeek
     * @return string
     */
    public function getDayOfWeekText(int $dayOfWeek): string
    {
        $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];

        if (isset($daysOfWeek[$dayOfWeek])) {
            return '（' . $daysOfWeek[$dayOfWeek] . '）';
        }

        return '';
    }
}
