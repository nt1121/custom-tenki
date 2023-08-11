<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class WeatherForecastService
{
    /**
     * 緯度と軽度でAPIから天気予報を取得する。キャッシュされている場合はキャッシュから取得する。
     * 
     * @param  int  $areaId 地域ID
     * @param  string $latitude 緯度
     * @param  string $longitude 軽度
     * @return array|bool
     */
    public function getWeatherForecast(int $areaId, string $latitude, string $longitude): array | bool
    {
        // キャッシュが存在する場合はキャッシュから取得
        $cacheKey = 'weather_forecast_data_area_id_' . $areaId;
        $result = Cache::get($cacheKey);

        if (is_null($result)) {
            if (RateLimiter::tooManyAttempts('weather-api-request', $perMinute = config('const.weather_api.max_requests_per_minute'))) {
                abort(429);
            }

            RateLimiter::hit('weather-api-request');
            $url = 'https://api.openweathermap.org/data/2.5/forecast?lat=' . $latitude . '&lon=' . $longitude . '&units=metric&cnt=40&lang=ja&appid=' . config('const.weather_api.api_key');
            $data = json_decode(file_get_contents($url), true);

            if (!isset($data['list'])) {
                return false;
            }

            $result = [];

            foreach ($data['list'] as $datum) {
                if (!isset($datum['dt'])) {
                    return false;
                }

                $dt = Carbon::createFromTimestamp($datum['dt']);
                $result[] = [
                    'dt' => $datum['dt'],
                    'date_key' => $dt->format('Ymd'),
                    'date_text' => $dt->format('Y年m月d日') . $this->getDayOfWeekText($dt->dayOfWeek),
                    'hour' => $dt->format('H'),
                    'temp' => isset($datum['main']['temp']) ? floor($datum['main']['temp']) : null,
                    'humidity' => $datum['main']['humidity'] ?? null,
                    'weather' => $datum['weather'][0]['icon'] ?? null,
                    'wind' => [
                        'speed' => $datum['wind']['speed'] ?? null,
                        'direction' => isset($datum['wind']['deg']) ? $this->convertWindDegToText($datum['wind']['deg']) : null,
                    ],
                    'pop' => isset($datum['pop']) ? floor($datum['pop'] * 100) : null,
                    'rain_3h' => $datum['rain']['3h'] ?? null,
                ];
            }

            // キャッシュに保存
            Cache::put($cacheKey, $result, 3600);
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
        }

        if ($deg < 33.75) {
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

    /**
     * APIから取得した天気予報のデータをホーム画面に表示する形式に整える
     * 
     * @param  App\Models\User $user
     * @patam  array $data
     * @return array
     */
    public function formatDataFromApi(User $user, array $data): array
    {
        $result = [];
        $limit = new Carbon('+4 days');
        $limit->setTime(0, 0, 0);
        $itemsToDisplay = $user->weatherForecastItems;
        $headers = array_merge(['時刻'], $itemsToDisplay->pluck('display_name')->toArray());
        $itemNamesToDisplay = array_merge(['hour'], $itemsToDisplay->pluck('name')->toArray());

        foreach ($data as $datum) {
            if ($datum['dt'] >= $limit->timestamp) {
                // 現在日から４日後以降は表示しない
                break;
            }

            if ($datum['dt'] <= time()) {
                // 現在時刻以前の場合は表示しない
                continue;
            }

            if (!isset($result[$datum['date_key']])) {
                $result[$datum['date_key']] = [
                    'date_text' => $datum['date_text'],
                    'headers' => $headers,
                    'value_list' => [],
                ];
            }

            $tmp = [];

            foreach ($itemNamesToDisplay as $itemName) {
                $value = $datum[$itemName];

                if ($itemName === 'hour') {
                    $tmp[] = $value;
                } elseif ($itemName === 'weather') {
                    $tmp[] = '<img class="p-wheather-forecast__table-td-icon" src="https://openweathermap.org/img/wn/' . $value . '.png" alt="天気">';
                } elseif ($itemName === 'temp') {
                    $tmp[] = $value . '℃';
                } elseif ($itemName === 'pop' || $itemName === 'humidity') {
                    $tmp[] = $value . '%';
                } elseif ($itemName === 'rain_3h') {
                    $tmp[] = is_null($value) ? '0mm/3h' : ($value . 'mm/3h');
                } elseif ($itemName === 'wind') {
                    $tmp[] = $value['speed'] . 'm/s<br>' . $value['direction'];
                } else {
                    // 不明な項目の場合は空欄
                    $tmp[] = '';
                }
            }

            $result[$datum['date_key']]['value_list'][] = $tmp;
        }

        return $result;
    }
}
