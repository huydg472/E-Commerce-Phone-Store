<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.view'])->get('/payments', [PaymentController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.create'])->post('/payments', [PaymentController::class, 'store']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.view'])->get('/payments/{payment}', [PaymentController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.update'])->put('/payments/{payment}', [PaymentController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:payments.delete'])->delete('/payments/{payment}', [PaymentController::class, 'destroy']);

Route::middleware('auth:sanctum')->post('/payments/{payment}/vnpay-url', [PaymentController::class, 'createVnpayUrl']);
