<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// トップページ
Route::get('/', [\App\Http\Controllers\TopController::class, 'index']);
// 利用規約
Route::get('/terms', [\App\Http\Controllers\TermsController::class, 'index']);
// プライバシーポリシー
Route::get('/privacy', [\App\Http\Controllers\PrivacyController::class, 'index']);
// テストユーザーとしてログイン
Route::post('/test_user_login', [\App\Http\Controllers\LoginController::class, 'loginAsTestUser']);
// ログイン
Route::get('/login', [\App\Http\Controllers\LoginController::class, 'show'])->name('login');
Route::post('/login', [\App\Http\Controllers\LoginController::class, 'login']);
// ログアウト
Route::post('/logout', [\App\Http\Controllers\LogoutController::class, 'logout']);
// 新規会員登録
Route::get('/register', [\App\Http\Controllers\RegisterController::class, 'show']);
Route::post('/register', [\App\Http\Controllers\RegisterController::class, 'register']);
Route::get('/register/{token}', [\App\Http\Controllers\RegisterController::class, 'complete']);
// パスワードの再設定
Route::get('/password_reset/request', [\App\Http\Controllers\PasswordResetController::class, 'showRequestPage']);
Route::post('/password_reset/request', [\App\Http\Controllers\PasswordResetController::class, 'request']);
Route::get('/password_reset/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetPage']);
Route::patch('/password_reset', [\App\Http\Controllers\PasswordResetController::class, 'reset']);
// メールアドレスの変更
Route::get('/email_change/{token}', [\App\Http\Controllers\EmailChangeController::class, 'changeUserEmail']);

Route::middleware(['auth', 'verified:login'])->group(function () {
    // SPAからリクエストするAPIのルート
    Route::middleware(['throttle:webapi'])->prefix('api')->group(function () {
        // ホーム
        Route::get('/weather', [\App\Http\Controllers\Api\WeatherController::class, 'getWeatherData']);
        // 設定
        Route::get('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'getSettingsData']);
        // 地域の選択
        Route::get('/settings/area_select/{id?}', [\App\Http\Controllers\Api\SettingsController::class, 'getAreaSelectData'])->where(['id' => '[1-9][0-9]*']);
        Route::patch('/users/area_id', [\App\Http\Controllers\Api\UserController::class, 'updateAreaId']);
        // 項目の選択
        Route::get('/settings/item_select', [\App\Http\Controllers\Api\SettingsController::class, 'getItemSelectData']);
        Route::put('/user_weather_forecast_item', [\App\Http\Controllers\Api\UserWeatherForecastItemController::class, 'deleteAndInsert']);
        // メールアドレスの変更
        Route::get('/settings/email_change', [\App\Http\Controllers\Api\SettingsController::class, 'getEmailChangeData']);
        Route::post('/users/email', [\App\Http\Controllers\Api\UserController::class, 'requestEmailChange']);
        // パスワードの変更
        Route::get('/settings/password_change', [\App\Http\Controllers\Api\SettingsController::class, 'getPasswordChangeData']);
        Route::patch('/users/password', [\App\Http\Controllers\Api\UserController::class, 'updatePassword']);
    });

    // SPA
    Route::get('/weather{any}', [\App\Http\Controllers\WeatherController::class, 'index'])->where('any', '.*');
    // アカウントの削除
    Route::get('/unregister', [\App\Http\Controllers\UnregisterController::class, 'show']);
    Route::delete('/unregister', [\App\Http\Controllers\UnregisterController::class, 'unregister']);
});
