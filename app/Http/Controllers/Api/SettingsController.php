<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AreaGroupService;
use App\Services\UserService;
use App\Services\WeatherForecastItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected AreaGroupService $areaGroupService,
        protected WeatherForecastItemService $weatherForecastItemService
    ) {
    }

    /**
     * 設定画面を表示するために必要な情報を取得する
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getSettingsData(): JsonResponse
    {
        $loginUser = Auth::user();
        return response()->json([
            'user' => $this->userService->getUserStoreState($loginUser),
            'items_to_display' => $this->weatherForecastItemService->getWeatherForecastItemsToDisplayByUser($loginUser),
        ]);
    }

    /**
     * 地域選択画面を表示するために必要な情報を取得する
     *
     * @param  stirng|null $id 地域グループID
     * @return Illuminate\Http\JsonResponse
     */
    public function getAreaSelectData(?string $id = null): JsonResponse
    {
        $id = is_null($id) ? null : (int) $id;
        $areaGroup = $this->areaGroupService->getAreaGroupAndChildren($id);

        if (!$areaGroup) {
            return response()->json([
                'status' => 404,
                'errors' => ['地域グループが取得できませんでした。'],
            ], 404);
        }

        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
            'area_group' => $areaGroup,
        ]);
    }

    /**
     * 表示項目選択画面を表示するために必要な情報を取得する
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getItemSelectData(): JsonResponse
    {
        $loginUser = Auth::user();
        return response()->json([
            'user' => $this->userService->getUserStoreState($loginUser),
            'items_to_display' => $this->weatherForecastItemService->getWeatherForecastItemsToDisplayByUser($loginUser),
            'items_to_hide' => $this->weatherForecastItemService->getWeatherForecastItemsToHideByUser($loginUser),
        ]);
    }

    /**
     * メールアドレス変更画面を表示するために必要な情報を取得する
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getEmailChangeData(): JsonResponse
    {
        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
        ]);
    }

    /**
     * パスワード変更画面を表示するために必要な情報を取得する
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getPasswordChangeData(): JsonResponse
    {
        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
        ]);
    }
}
