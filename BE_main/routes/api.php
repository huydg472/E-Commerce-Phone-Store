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
use App\Http\Controllers\Web\AuthTestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::patch('/brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus']);
Route::apiResource('brands', BrandController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'show']);
    Route::delete('/cart', [CartController::class, 'clear']);

    Route::post('/cart/items', [CartItemController::class, 'store']);
    Route::put('/cart/items/{cartItem}', [CartItemController::class, 'update']);
    Route::delete('/cart/items/{cartItem}', [CartItemController::class, 'destroy']);
});

Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
Route::get('/categories/by-slug/{slug}', [CategoryController::class, 'showBySlug']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

Route::apiResource('order', OrderController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('orderitem', OrderItemController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('payment', PaymentController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('permission', PermissionController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

Route::get('/products/by-slug/{slug}', [ProductController::class, 'showBySlug']);
Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
Route::apiResource('products', ProductController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

Route::apiResource('product-specifications', ProductSpecificationController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::get('/product-variants/by-sku/{sku}', [ProductVariantController::class, 'showBySku']);
Route::patch('/product-variants/{productVariant}/toggle-status', [ProductVariantController::class, 'toggleStatus']);
Route::apiResource('product-variants', ProductVariantController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('product-variant-images', ProductVariantImageController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

Route::apiResource('role', RoleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('shippingaddress', ShippingAddressController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('stocklog', StockLogController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('user', UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });
});

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
