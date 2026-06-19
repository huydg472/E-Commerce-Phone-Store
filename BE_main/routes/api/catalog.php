<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NewsCategoryController;
use App\Http\Controllers\NewsPostController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}', [BrandController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:brands.create'])->post('/brands', [BrandController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:brands.update'])->patch('/brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:brands.update'])->put('/brands/{brand}', [BrandController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:brands.delete'])->delete('/brands/{brand}', [BrandController::class, 'destroy']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/by-slug/{slug}', [CategoryController::class, 'showBySlug']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:categories.create'])->post('/categories', [CategoryController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:categories.update'])->patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:categories.update'])->put('/categories/{category}', [CategoryController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:categories.delete'])->delete('/categories/{category}', [CategoryController::class, 'destroy']);

Route::get('/news/categories', [NewsCategoryController::class, 'publicIndex']);
Route::get('/news/categories/{slug}', [NewsCategoryController::class, 'publicShowBySlug']);
Route::get('/news/posts', [NewsPostController::class, 'publicIndex']);
Route::get('/news/posts/{slug}', [NewsPostController::class, 'publicShowBySlug']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/by-slug/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:products.create'])->post('/products', [ProductController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:products.update'])->patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:products.update'])->put('/products/{product}', [ProductController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:products.delete'])->delete('/products/{product}', [ProductController::class, 'destroy']);
