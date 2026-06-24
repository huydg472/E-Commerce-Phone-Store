<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    /**
     * Login
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::with('role.permissions')->where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không khớp với dữ liệu của chúng tôi.',
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            $sent = $user->sendEmailVerificationNotification();

            $message = $sent
                ? 'Email chưa được xác minh. Chúng tôi đã gửi email xác minh mới. Vui lòng kiểm tra hộp thư và xác minh trong vòng 5 phút.'
                : 'Email chưa được xác minh. Email xác minh gần nhất vẫn còn hiệu lực trong 5 phút. Vui lòng kiểm tra hộp thư để xác minh tài khoản.';

            return response()->json([
                'message' => $message,
                'verification_required' => true,
                'user' => $user->load('role'),
            ], 409);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Logout
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Đăng xuất thành công',
        ]);
    }
}
