<?php

use App\Http\Controllers\CouponController;
use App\Http\Controllers\NewsCategoryController;
use App\Http\Controllers\NewsPostController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductSpecificationController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProductVariantImageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum', 'role:admin,staff'])->group(function () {
    Route::middleware('permission:news_categories.view')->get('/admin/news/categories', [NewsCategoryController::class, 'adminIndex']);
    Route::middleware('permission:news_categories.create')->post('/admin/news/categories', [NewsCategoryController::class, 'store']);
    Route::middleware('permission:news_categories.view')->get('/admin/news/categories/{newsCategory}', [NewsCategoryController::class, 'show']);
    Route::middleware('permission:news_categories.update')->put('/admin/news/categories/{newsCategory}', [NewsCategoryController::class, 'update']);
    Route::middleware('permission:news_categories.update')->patch('/admin/news/categories/{newsCategory}/toggle-status', [NewsCategoryController::class, 'toggleStatus']);
    Route::middleware('permission:news_categories.delete')->delete('/admin/news/categories/{newsCategory}', [NewsCategoryController::class, 'destroy']);

    Route::middleware('permission:news_posts.view')->get('/admin/news/posts', [NewsPostController::class, 'adminIndex']);
    Route::middleware('permission:news_posts.create')->post('/admin/news/posts', [NewsPostController::class, 'store']);
    Route::middleware('permission:news_posts.view')->get('/admin/news/posts/{newsPost}', [NewsPostController::class, 'show']);
    Route::middleware('permission:news_posts.update')->put('/admin/news/posts/{newsPost}', [NewsPostController::class, 'update']);
    Route::middleware('permission:news_posts.update')->patch('/admin/news/posts/{newsPost}/toggle-status', [NewsPostController::class, 'toggleStatus']);
    Route::middleware('permission:news_posts.delete')->delete('/admin/news/posts/{newsPost}', [NewsPostController::class, 'destroy']);

    Route::get('/reports/revenue', [ReportController::class, 'revenue']);
    Route::get('/reports/products', [ReportController::class, 'products']);
    Route::get('/reports/orders', [ReportController::class, 'orders']);
});
