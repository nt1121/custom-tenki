<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\WeatherForecastItem;
use Illuminate\Support\Arr;

class WeatherForecastItemService
{
    public function getWeatherForecastItemsToDisplayByUser(User $user)
    {
        $result = [];
        $items = $user->weatherForecastItems->toArray();

        foreach ($items as $item) {
            $result[] = Arr::only($item, ['id', 'name', 'display_name']);
        }

        return $result;
    }

    public function getWeatherForecastItemsToHideByUser(User $user)
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
