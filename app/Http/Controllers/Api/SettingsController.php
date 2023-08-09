<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AreaGroupService;
use App\Services\UserService;
use App\Services\WeatherForecastItemService;
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
     */
    public function getSettingsData()
    {
        $loginUser = Auth::user();
        return response()->json([
            'user' => $this->userService->getUserStoreState($loginUser),
            'items_to_display' => $this->weatherForecastItemService->getWeatherForecastItemsToDisplayByUser($loginUser),
        ]);
    }

    /**
     * 地域選択画面を表示するために必要な情報を取得する
     */

    public function getAreaSelectData(?string $id = null)
    {
        $id = is_null($id) ? null : (int)$id;
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
     */
    public function getItemSelectData()
    {
        $loginUser = Auth::user();
        return response()->json([
            'user' => $this->userService->getUserStoreState($loginUser),
            'items_to_display' => $this->weatherForecastItemService->getWeatherForecastItemsToDisplayByUser($loginUser),
            'items_to_hide' => $this->weatherForecastItemService->getWeatherForecastItemsToHideByUser($loginUser),
        ]);
    }

    public function getEmailChangeData()
    {
        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
        ]);
    }

    public function getPasswordChangeData()
    {
        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
        ]);
    }

}
