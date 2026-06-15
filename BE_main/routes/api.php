<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
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
    return $request->user()->load('role.permissions');
});

Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}', [BrandController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:brands.create'])->post('/brands', [BrandController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:brands.update'])->patch('/brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:brands.update'])->put('/brands/{brand}', [BrandController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:brands.delete'])->delete('/brands/{brand}', [BrandController::class, 'destroy']);

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
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/by-slug/{slug}', [CategoryController::class, 'showBySlug']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:categories.create'])->post('/categories', [CategoryController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:categories.update'])->patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:categories.update'])->put('/categories/{category}', [CategoryController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:categories.delete'])->delete('/categories/{category}', [CategoryController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/mock-payment', [OrderController::class, 'mockPayment']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
});

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.update'])->put('/orders/{order}', [OrderController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.delete'])->delete('/orders/{order}', [OrderController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.update'])->get('/order-items', [OrderItemController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.update'])->post('/order-items', [OrderItemController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.update'])->get('/order-items/{orderItem}', [OrderItemController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.update'])->put('/order-items/{orderItem}', [OrderItemController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.update'])->delete('/order-items/{orderItem}', [OrderItemController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.view'])->get('/payments', [PaymentController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.create'])->post('/payments', [PaymentController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.view'])->get('/payments/{payment}', [PaymentController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.update'])->put('/payments/{payment}', [PaymentController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.delete'])->delete('/payments/{payment}', [PaymentController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:coupons.view'])->get('/coupons', [CouponController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:coupons.create'])->post('/coupons', [CouponController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:coupons.view'])->get('/coupons/{coupon}', [CouponController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:coupons.update'])->put('/coupons/{coupon}', [CouponController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:coupons.update'])->patch('/coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:coupons.delete'])->delete('/coupons/{coupon}', [CouponController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:permissions.view'])->get('/permissions', [PermissionController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:permissions.create'])->post('/permissions', [PermissionController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:permissions.view'])->get('/permissions/{permission}', [PermissionController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:permissions.update'])->put('/permissions/{permission}', [PermissionController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:permissions.delete'])->delete('/permissions/{permission}', [PermissionController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_specifications.view'])->get('/product-specifications', [ProductSpecificationController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_specifications.create'])->post('/product-specifications', [ProductSpecificationController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_specifications.view'])->get('/product-specifications/{productSpecification}', [ProductSpecificationController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_specifications.update'])->put('/product-specifications/{productSpecification}', [ProductSpecificationController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_specifications.delete'])->delete('/product-specifications/{productSpecification}', [ProductSpecificationController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variants.view'])->get('/product-variants', [ProductVariantController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variants.create'])->post('/product-variants', [ProductVariantController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variants.view'])->get('/product-variants/by-sku/{sku}', [ProductVariantController::class, 'showBySku']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variants.view'])->get('/product-variants/{productVariant}', [ProductVariantController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variants.update'])->patch('/product-variants/{productVariant}/toggle-status', [ProductVariantController::class, 'toggleStatus']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variants.update'])->put('/product-variants/{productVariant}', [ProductVariantController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variants.delete'])->delete('/product-variants/{productVariant}', [ProductVariantController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variant_images.view'])->get('/product-images', [ProductVariantImageController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variant_images.create'])->post('/product-images', [ProductVariantImageController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variant_images.view'])->get('/product-images/{productVariantImage}', [ProductVariantImageController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variant_images.update'])->put('/product-images/{productVariantImage}', [ProductVariantImageController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:product_variant_images.delete'])->delete('/product-images/{productVariantImage}', [ProductVariantImageController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:roles.view'])->get('/roles', [RoleController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:roles.create'])->post('/roles', [RoleController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:roles.view'])->get('/roles/{role}', [RoleController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:roles.update'])->put('/roles/{role}', [RoleController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:roles.delete'])->delete('/roles/{role}', [RoleController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:stock_logs.view'])->get('/stock-logs', [StockLogController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:stock_logs.create'])->post('/stock-logs', [StockLogController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:stock_logs.view'])->get('/stock-logs/{stockLog}', [StockLogController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:stock_logs.update'])->put('/stock-logs/{stockLog}', [StockLogController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:stock_logs.delete'])->delete('/stock-logs/{stockLog}', [StockLogController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:users.view'])->get('/users', [UserController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:users.create'])->post('/users', [UserController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:users.view'])->get('/users/{user}', [UserController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:users.update'])->put('/users/{user}', [UserController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:users.delete'])->delete('/users/{user}', [UserController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/{payment}/vnpay-url', [PaymentController::class, 'createVnpayUrl']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::get('/favorites/{productVariant}/status', [FavoriteController::class, 'status']);
    Route::post('/favorites/{productVariant}/toggle', [FavoriteController::class, 'toggle']);
    Route::delete('/favorites/{productVariant}', [FavoriteController::class, 'destroy']);

    Route::get('/shipping-addresses', [ShippingAddressController::class, 'index']);
    Route::post('/shipping-addresses', [ShippingAddressController::class, 'store']);
    Route::get('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'show']);
    Route::put('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'update']);
    Route::delete('/shipping-addresses/{shippingAddress}', [ShippingAddressController::class, 'destroy']);

    Route::post('/coupons/apply', [CouponController::class, 'apply']);

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/me', function (Request $request) {
        return response()->json($request->user()->load('role.permissions'));
    });
});

Route::middleware(['auth:sanctum', 'role:admin,staff'])->group(function () {
    Route::get('/reports/revenue', [ReportController::class, 'revenue']);
    Route::get('/reports/products', [ReportController::class, 'products']);
    Route::get('/reports/orders', [ReportController::class, 'orders']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/by-slug/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:products.create'])->post('/products', [ProductController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:products.update'])->patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:products.update'])->put('/products/{product}', [ProductController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:products.delete'])->delete('/products/{product}', [ProductController::class, 'destroy']);

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
