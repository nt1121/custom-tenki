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

        if (!$area) {
            return response()->json([
                'status' => 404,
                'errors' => ['地域が取得できませんでした。'],
            ], 404);
        }

        $data = $this->weatherForecastService->getWeatherForecast($area->id, $area->latitude, $area->longitude);

        if (!$data) {
            abort(400);
        }

        $list = $this->weatherForecastService->formatDataFromApi($loginUser, $data);

        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
            'area' => Arr::only($area->toArray(), ['id', 'name']),
            'list' => $list, 
        ]);
    }
}
