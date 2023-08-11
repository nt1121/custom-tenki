<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\WeatherForecastItem;
use Illuminate\Support\Arr;

class WeatherForecastItemService
{
    /**
     * ユーザーが表示する天気予報の項目の配列を取得する
     * 
     * @param  App\Models\User $user
     * @return array
     */
    public function getWeatherForecastItemsToDisplayByUser(User $user): array
    {
        $result = [];
        $items = $user->weatherForecastItems->toArray();

        foreach ($items as $item) {
            $result[] = Arr::only($item, ['id', 'name', 'display_name']);
        }

        return $result;
    }

    /**
     * ユーザーが表示しない天気予報の項目の配列を取得する
     * 
     * @param  App\Models\User $user
     * @return array
     */
    public function getWeatherForecastItemsToHideByUser(User $user): array
    {
        $result = [];
        $itemIdsToDisplay = $user->weatherForecastItems->pluck('id')->toArray();

        if (count($itemIdsToDisplay)) {
            $items = WeatherForecastItem::whereNotIn('id', $itemIdsToDisplay)->orderBy('display_order', 'asc')->get()->toArray();
        } else {
            $items = WeatherForecastItem::orderBy('display_order', 'asc')->get()->toArray();
        }

        foreach ($items as $item) {
            $result[] = Arr::only($item, ['id', 'name', 'display_name']);
        }

        return $result;
    }

}
