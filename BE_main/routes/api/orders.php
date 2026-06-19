<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/mock-payment', [OrderController::class, 'mockPayment']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
});

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.update'])->put('/orders/{order}', [OrderController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:orders.delete'])->delete('/orders/{order}', [OrderController::class, 'destroy']);
