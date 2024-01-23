<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Services\WeatherForecastService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected WeatherForecastService $weatherForecastService
    ) {
    }

    /**
     * ホーム画面を表示するために必要な情報を取得する
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function getWeatherData(): JsonResponse
    {
        $loginUser = Auth::user();

        if (is_null($loginUser->area_id)) {
            return response()->json([
                'user' => $this->userService->getUserStoreState(Auth::user()),
                'area' => null,
                'list' => null,
            ]);
        }

        $area = $loginUser->area;

        // 外部キー制約があるので、取得できないことは基本的にないはず
        if (!$area) {
            return response()->json([
                'status' => 404,
                'errors' => ['地域が取得できませんでした。'],
            ], 404);
        }

        // キャッシュからAPIのレスポンスを取得する
        $data = $this->weatherForecastService->getThreeHourForecastDataFromCache($area->id);

        // キャッシュされていなかった場合はAPIにリクエストする
        if (is_null($data)) {
            $data = $this->weatherForecastService->makeRequestToThreeHourForecastApi($area->id, $area->latitude, $area->longitude);
        }

        // APIから天気予報データが取得できなかった場合
        if (!$data) {
            return response()->json([
                'status' => 400,
                'errors' => ['天気予報データが取得できませんでした。'],
            ], 400);
        }

        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
            'area' => Arr::only($area->toArray(), ['id', 'name']),
            'list' => $this->weatherForecastService->createThreeHourForecastDataForDisplay($loginUser, $data)
        ]);
    }
}
