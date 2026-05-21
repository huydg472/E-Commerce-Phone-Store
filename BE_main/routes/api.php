<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSpecificationController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProductVariantImageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('brand', BrandController::class)->only([
    'index', //ok
    'store', //ok
    'show', //ok
    'update', //ok
    'destroy' //ok
]);

Route::apiResource('cart', CartController::class)->only([
    'index', //ok
    'store', //ok
    'show', //ok
    'update', //ok
    'destroy' //ok
]);


Route::apiResource('cartitem', CartItemController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('category', CategoryController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('order', OrderController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('orderitem', OrderItemController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('payment', PaymentController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('permission', PermissionController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('product', ProductController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('productspecification', ProductSpecificationController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('productvariant', ProductVariantController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('productvariantimage', ProductVariantImageController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('role', RoleController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('shippingaddress', ShippingAddressController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('stocklog', StockLogController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::apiResource('user', UserController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    Route::get('/me', function (Request $request) {

        return response()->json($request->user());
    });
});
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);

Route::post('/reset-password', [NewPasswordController::class, 'store']);
