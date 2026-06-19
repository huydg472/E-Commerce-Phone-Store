<?php

use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:settings.view'])->get('/admin/settings', [SettingController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff', 'permission:settings.update'])->put('/admin/settings', [SettingController::class, 'update']);
