<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('verification.send');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user()->load('role.permissions');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/me', function (Request $request) {
        return response()->json($request->user()->load('role.permissions'));
    });

    Route::put('/me', function (Request $request) {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:225',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'birthday' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:20'],
        ]);

        $emailChanged = strcasecmp((string)$data['email'], (string)$user->email) !== 0;

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);
        $user->refresh()->load('role.permissions');

        if ($emailChanged) {
            $sent = $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => $sent
                    ? 'Cập nhật thành công. Đã gửi email xác minh tới địa chỉ mới.'
                    : 'Cập nhật thành công. Email xác minh gần nhất vẫn còn hiệu lực trong 5 phút.',
                'user' => $user,
            ]);
        }

        return response()->json([
            'message' => 'Cập nhật thành công.',
            'user' => $user,
        ]);
    });
});

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
