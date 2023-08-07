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

Route::get('/', [\App\Http\Controllers\TopController::class, 'index']);
Route::get('/terms', [\App\Http\Controllers\TermsController::class, 'index']);
Route::get('/privacy', [\App\Http\Controllers\PrivacyController::class, 'index']);
Route::get('/login', [\App\Http\Controllers\LoginController::class, 'show'])->name('login');
Route::post('/login', [\App\Http\Controllers\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\LogoutController::class, 'logout']);
Route::get('/register', [\App\Http\Controllers\RegisterController::class, 'show']);
Route::post('/register', [\App\Http\Controllers\RegisterController::class, 'register']);
Route::get('/register/{token}', [\App\Http\Controllers\RegisterController::class, 'complete']);
Route::get('/password_reset/request', [\App\Http\Controllers\PasswordResetController::class, 'showRequestPage']);
Route::post('/password_reset/request', [\App\Http\Controllers\PasswordResetController::class, 'request']);
Route::get('/password_reset/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetPage']);
Route::patch('/password_reset', [\App\Http\Controllers\PasswordResetController::class, 'reset']);

Route::middleware(['auth', 'verified:login'])->group(function () {
    Route::middleware(['throttle:webapi'])->prefix('api')->group(function () {
        Route::patch('/users/area_id', [\App\Http\Controllers\Api\UserController::class, 'updateAreaId']);
        Route::put('/user_weather_forecast_item', [\App\Http\Controllers\Api\UserWeatherForecastItemController::class, 'deleteAndInsert']);
        Route::get('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'getSettingsData']);
        Route::get('/settings/area_select/{id?}', [\App\Http\Controllers\Api\SettingsController::class, 'getAreaSelectData'])->where(['id' => '[1-9][0-9]*']);
        Route::get('/settings/item_select', [\App\Http\Controllers\Api\SettingsController::class, 'getItemSelectData']);

    });

    Route::get('/weather{any}', [\App\Http\Controllers\WeatherController::class, 'index'])->where('any', '.*');
});
