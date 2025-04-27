<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\ImgurController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseDetailController;
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
    Route::get('my-order/{code}', [OrderController::class, 'getMyOrder']);
});

Route::middleware('auth.api.admin')->prefix('admin/v1')->group(function () {
    // Route::get('my-order/{code}', [OrderController::class, 'getMyOrder']);
    // Orders
    Route::get('list-orders', [OrderController::class, 'getAllOrders']);
    Route::get('order-detail/{code}', [OrderController::class, 'getOrderDetail']);
    Route::put('order-update/{code}', [OrderController::class, 'updateOrder']);
    Route::put('order-update-status/{code}', [OrderController::class, 'updateStatusOrder']);

    // Product

    Route::get('list-products', [ProductController::class, 'getAllProducts']);
    Route::get('product/{code}', [ProductController::class, 'getDetailProduct']);
    Route::post('product', [ProductController::class, 'createProduct']);
    Route::put('product', [ProductController::class, 'updateProduct']);
    Route::delete('product/{code}', [ProductController::class, 'deleteProduct']);

    // Variant
    Route::get('list-variants', [VariantController::class, 'getAllVariants']);
    Route::get('variant/{id}', [VariantController::class, 'getDetailVariant']);
    Route::post('variant', [VariantController::class, 'createVariant']);
    Route::put('variant', [VariantController::class, 'updateVariant']);
    Route::delete('variant/{id}', [VariantController::class, 'deleteVariant']);

    // Variant
    Route::get('list-categories', [CategoryController::class, 'getAllCategories']);
    Route::get('category/{code}', [CategoryController::class, 'getDetailCategory']);
    Route::post('category', [CategoryController::class, 'createCategory']);
    Route::put('category', [CategoryController::class, 'updateCategory']);
    Route::delete('category/{code}', [CategoryController::class, 'deleteCategory']);

    // Discount
    Route::get('list-discounts', [DiscountController::class, 'getAllDiscounts']);
    Route::get('discount/{code}', [DiscountController::class, 'getDetailDiscount']);
    Route::post('discount', [DiscountController::class, 'createDiscount']);
    Route::put('discount', [DiscountController::class, 'updateDiscount']);
    Route::delete('discount/{code}', [DiscountController::class, 'deleteDiscount']);

    // Promotion
    Route::get('list-promotions', [PromotionController::class, 'getAllPromotions']);
    Route::get('promotion/{code}', [PromotionController::class, 'getDetailPromotion']);
    Route::post('promotion', [PromotionController::class, 'createPromotion']);
    Route::put('promotion', [PromotionController::class, 'updatePromotion']);
    Route::delete('promotion/{code}', [PromotionController::class, 'deletePromotion']);

    // Promotion
    Route::get('list-inventories', [InventoryController::class, 'getAllInventories']);
    Route::get('inventory/{id}', [InventoryController::class, 'getDetailInventory']);
    Route::post('inventory', [InventoryController::class, 'createInventory']);
    Route::put('inventory', [InventoryController::class, 'updateInventory']);
    Route::delete('inventory/{id}', [InventoryController::class, 'deleteInventory']);

    // Warehouse
    Route::get('list-warehouses', [WarehouseController::class, 'getAllWarehouses']);
    Route::get('warehouse/{id}', [WarehouseController::class, 'getDetailWarehouse']);
    Route::post('warehouse', [WarehouseController::class, 'createWarehouse']);
    Route::put('warehouse', [WarehouseController::class, 'updateWarehouse']);
    Route::delete('warehouse/{code}', [WarehouseController::class, 'deleteWarehouse']);

    // WarehouseDetail
    Route::get('list-warehouse-details', [WarehouseDetailController::class, 'getAllWarehouseDetails']);
    Route::get('warehouse-detail/{id}', [WarehouseDetailController::class, 'getDetailWarehouseDetail']);
    Route::post('warehouse-detail', [WarehouseDetailController::class, 'createWarehouseDetail']);
    Route::put('warehouse-detail', [WarehouseDetailController::class, 'updateWarehouseDetail']);
    Route::delete('warehouse-detail/{id}', [WarehouseDetailController::class, 'deleteWarehouseDetail']);

    // Account
    Route::get('list-accounts', [AccountController::class, 'getAllAccounts']);
    Route::get('account/{id}', [AccountController::class, 'getDetailAccount']);
    Route::post('account', [AccountController::class, 'createAccount']);
    Route::put('account', [AccountController::class, 'updateAccount']);
    Route::put('account/change-password', [AccountController::class, 'changePasswordAccount']);
    Route::delete('account/{id}', [AccountController::class, 'deleteAccount']);

    // Role
    Route::get('list-roles', [RoleController::class, 'getAllRoles']);
    Route::get('role/{code}', [RoleController::class, 'getDetailRole']);
    Route::post('role', [RoleController::class, 'createRole']);
    Route::put('role', [RoleController::class, 'updateRole']);
    Route::delete('role/{code}', [RoleController::class, 'deleteRole']);
    // Statistic
    Route::get('statictis-revenue-week', [OrderController::class, 'getStatisticRevenueByWeek']);
    Route::get('statictis-revenue-month', [OrderController::class, 'getStatisticRevenueByMonth']);
    Route::get('statictis-new-orders-week', [OrderController::class, 'getStatisticNewOrdersByWeek']);
    Route::get('statictis-new-orders-month', [OrderController::class, 'getStatisticNewOrdersByMonth']);
    Route::get('statictis-new-customer-week', [OrderController::class, 'getStatisticNewCustomerByWeek']);

    Route::get('statistic-discounts', [StatisticController::class, 'getStatisticDiscounts']);
});
