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

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}', [BrandController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/brands', [BrandController::class, 'store']);
    Route::patch('/brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus']);
    Route::put('/brands/{brand}', [BrandController::class, 'update']);
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'show']);
    Route::delete('/cart', [CartController::class, 'destroy']);

    Route::post('/cart/items', [CartItemController::class, 'store']);
    Route::put('/cart/items/{cartItem}', [CartItemController::class, 'update']);
    Route::delete('/cart/items/{cartItem}', [CartItemController::class, 'destroy']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/by-slug/{slug}', [CategoryController::class, 'showBySlug']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    Route::get('/order-items', [OrderItemController::class, 'index']);
    Route::post('/order-items', [OrderItemController::class, 'store']);
    Route::get('/order-items/{orderItem}', [OrderItemController::class, 'show']);
    Route::put('/order-items/{orderItem}', [OrderItemController::class, 'update']);
    Route::delete('/order-items/{orderItem}', [OrderItemController::class, 'destroy']);

    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    Route::put('/payments/{payment}', [PaymentController::class, 'update']);
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);

    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::get('/permissions/{permission}', [PermissionController::class, 'show']);
    Route::put('/permissions/{permission}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/by-slug/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/product-specifications', [ProductSpecificationController::class, 'index']);
    Route::post('/product-specifications', [ProductSpecificationController::class, 'store']);
    Route::get('/product-specifications/{productSpecification}', [ProductSpecificationController::class, 'show']);
    Route::put('/product-specifications/{productSpecification}', [ProductSpecificationController::class, 'update']);
    Route::delete('/product-specifications/{productSpecification}', [ProductSpecificationController::class, 'destroy']);

    Route::get('/product-variants', [ProductVariantController::class, 'index']);
    Route::post('/product-variants', [ProductVariantController::class, 'store']);
    Route::get('/product-variants/by-sku/{sku}', [ProductVariantController::class, 'showBySku']);
    Route::get('/product-variants/{productVariant}', [ProductVariantController::class, 'show']);
    Route::patch('/product-variants/{productVariant}/toggle-status', [ProductVariantController::class, 'toggleStatus']);
    Route::put('/product-variants/{productVariant}', [ProductVariantController::class, 'update']);
    Route::delete('/product-variants/{productVariant}', [ProductVariantController::class, 'destroy']);

    Route::get('/product-images', [ProductVariantImageController::class, 'index']);
    Route::post('/product-images', [ProductVariantImageController::class, 'store']);
    Route::get('/product-images/{productVariantImage}', [ProductVariantImageController::class, 'show']);
    Route::put('/product-images/{productVariantImage}', [ProductVariantImageController::class, 'update']);
    Route::delete('/product-images/{productVariantImage}', [ProductVariantImageController::class, 'destroy']);

    Route::get('/product-variant-images', [ProductVariantImageController::class, 'index']);
    Route::post('/product-variant-images', [ProductVariantImageController::class, 'store']);
    Route::get('/product-variant-images/{productVariantImage}', [ProductVariantImageController::class, 'show']);
    Route::put('/product-variant-images/{productVariantImage}', [ProductVariantImageController::class, 'update']);
    Route::delete('/product-variant-images/{productVariantImage}', [ProductVariantImageController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

    Route::get('/shipping-addresses', [ShippingAddressController::class, 'index']);
    Route::post('/shipping-addresses', [ShippingAddressController::class, 'store']);
    Route::get('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'show']);
    Route::put('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'update']);
    Route::delete('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'destroy']);

    Route::get('/stock-logs', [StockLogController::class, 'index']);
    Route::post('/stock-logs', [StockLogController::class, 'store']);
    Route::get('/stock-logs/{stockLog}', [StockLogController::class, 'show']);
    Route::put('/stock-logs/{stockLog}', [StockLogController::class, 'update']);
    Route::delete('/stock-logs/{stockLog}', [StockLogController::class, 'destroy']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });
});

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
