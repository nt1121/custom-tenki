<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopController;
use App\Http\Controllers\AuthController;

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

Route::get('/',  [TopController::class, 'index']);
Route::get('/terms',  [TopController::class, 'showTermsOfServicePage']);
Route::get('/privacy',  [TopController::class, 'showPrivacyPolicyPage']);
Route::get('/login',  [AuthController::class, 'showLoginPage'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout',  [AuthController::class, 'logout']);
Route::get('/register',  [AuthController::class, 'showRegisterPage']);
Route::post('/register',  [AuthController::class, 'register']);
Route::get('/register/{token}', [AuthController::class, 'verifyUserRegistrationToken']);
Route::get('/password_reset_request',  [AuthController::class, 'showPasswordResetRequestPage']);
Route::post('/password_reset_request',  [AuthController::class, 'requestPasswordReset']);
Route::get('/password_reset/{token}',  [AuthController::class, 'verifyPasswordResetRequestToken']);
Route::post('/password_reset',  [AuthController::class, 'resetPassword']);

Route::middleware(['auth', 'verified:login'])->group(function () {
    Route::prefix('api')->group(function () {

    });
});
