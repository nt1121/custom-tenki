<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserWeatherForecastItemPutRequest;
use App\Services\UserService;
use App\Services\UserWeatherForecastItemService;
use App\Services\WeatherForecastItemService;
use Illuminate\Http\JsonResponse;

class UserWeatherForecastItemController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected WeatherForecastItemService $weatherForecastItemService,
        protected UserWeatherForecastItemService $userWeatherForecastItemService
    ) {
    }

    /**
     * 会員と天気予報の項目の中間テーブルを更新する
     * 
     * @param  App\Http\Requests\Api\UserWeatherForecastItemPutRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function deleteAndInsert(UserWeatherForecastItemPutRequest $request): JsonResponse
    {
        $result = $this->userWeatherForecastItemService
            ->updateWeatherForecastItemsToDisplay($request->user_id, $request->item_ids_to_display);

        if (!$result) {
            return response()->json([
                'status' => 400,
                'errors' => ['情報の更新に失敗しました。'],
            ], 400);
        }

        return response()->json($result);
    }
}
