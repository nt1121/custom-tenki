<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWeatherForecastItem;
use Illuminate\Support\Facades\DB;

class UserWeatherForecastItemService
{
    /**
     * 会員と天気予報の項目の中間テーブルを更新する
     *
     * @param  int $userId 会員ID
     * @param  array $itemIdsToDisplay 表示する項目のIDの配列
     * @return array|bool
     */
    public function updateWeatherForecastItemsToDisplay(int $userId, array $itemIdsToDisplay): array | bool
    {
        try {
            DB::beginTransaction();
            DB::table('user_weather_forecast_item')->where('user_id', $userId)->delete();
            $displayOrder = 0;
            $result = [
                'user_weather_forecast_item' => [],
            ];

            foreach ($itemIdsToDisplay as $itemId) {
                $displayOrder++;
                $result['user_weather_forecast_item'][] =
                UserWeatherForecastItem::create(['user_id' => $userId, 'weather_forecast_item_id' => $itemId, 'display_order' => $displayOrder])->toArray();
            }

            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        return $result;
    }
}
