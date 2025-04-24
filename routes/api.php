<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\ImgurController;
use App\Http\Controllers\OrderController;
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
    // Product
    Route::get('products', [VariantController::class, 'getVariantProduct']);
    Route::get('product/{variant_id}', [VariantController::class, 'detailVariantProduct']);

    // Cart
    Route::get('cart', [CartController::class, 'getCart']);
    Route::post('add-to-cart', [CartController::class, 'addToCart']);
    Route::post('add-discount', [CartController::class, 'addDiscount']);
    Route::put('update-cart-info', [CartController::class, 'updateCartInfo']);

    // Order
    Route::post('confirm-order', [OrderController::class, 'confirmOrder']);

    // Upload And Get Image
    Route::post('/upload-image', [ImgurController::class, 'uploadImage']);
    Route::get('/proxy-image', [ImageProxyController::class, 'fetchImage']);
});

Route::middleware('auth.api.client')->prefix('client/v1')->group(function () {
    Route::get('my-orders', [OrderController::class, 'getMyOrders']);
});
