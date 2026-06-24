<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (!$user) {
            $credentials = $request->validate([
                'username' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            $user = User::query()
                ->where('username', $credentials['username'])
                ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Thông tin đăng nhập không hợp lệ.',
                ], 401);
            }
        }

        if ($user->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? response()->json(['status' => 'already-verified'])
                : redirect()->intended(config('app.frontend_url') . '/auth/login?verified=1');
        }

        $sent = $user->sendEmailVerificationNotification();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $sent ? 'verification-link-sent' : 'verification-link-still-valid',
                'message' => $sent
                    ? 'Đã gửi email xác minh mới.'
                    : 'Email xác minh gần nhất vẫn còn hiệu lực trong 5 phút.',
            ]);
        }

        return redirect()->intended(
            config('app.frontend_url') . '/auth/login?' . ($sent ? 'verification-sent=1' : 'verification-still-valid=1')
        );
    }
}
