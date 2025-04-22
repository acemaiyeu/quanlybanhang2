<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\ImgurController;
use App\Http\Controllers\VariantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | API Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register API routes for your application. These
 * | routes are loaded by the RouteServiceProvider and all of them will
 * | be assigned to the "api" middleware group. Make something great!
 * |
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);
});

Route::prefix('v0')->group(function () {
    Route::get('products', [VariantController::class, 'getVariantProduct']);
    Route::post('/upload-image', [ImgurController::class, 'uploadImage']);
    Route::get('/proxy-image', [ImageProxyController::class, 'fetchImage']);
});
