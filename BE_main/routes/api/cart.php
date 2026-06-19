<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ShippingAddressController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{cart}', [CartController::class, 'update']);
    Route::delete('/cart', [CartController::class, 'destroy']);

    Route::get('/cart/items', [CartItemController::class, 'index']);
    Route::post('/cart/items', [CartItemController::class, 'store']);
    Route::get('/cart/items/{cartItem}', [CartItemController::class, 'show']);
    Route::put('/cart/items/{cartItem}', [CartItemController::class, 'update']);
    Route::delete('/cart/items/{cartItem}', [CartItemController::class, 'destroy']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{productVariant}/toggle', [FavoriteController::class, 'toggle']);

    Route::get('/shipping-addresses', [ShippingAddressController::class, 'index']);
    Route::post('/shipping-addresses', [ShippingAddressController::class, 'store']);
    Route::get('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'show']);
    Route::put('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'update']);
    Route::delete('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'destroy']);

    Route::post('/coupons/apply', [CouponController::class, 'apply']);
});
