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
        Route::get('/users/store_state', [\App\Http\Controllers\Api\UserController::class, 'getUserStoreState']);
        Route::get('/area_groups/{id?}', [\App\Http\Controllers\Api\AreaGroupController::class, 'getAreaGroupAndChildren']);
        Route::patch('/users/area_id', [\App\Http\Controllers\Api\UserController::class, 'updateAreaId']);
    });

    Route::get('/weather{any}', [\App\Http\Controllers\WeatherController::class, 'index'])->where('any', '.*');
});
