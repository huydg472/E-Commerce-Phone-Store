<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    /**
     * Login
     */
    public function store(Request $request)
    { // validate các trường khi đăng nhập gồm email và pass
        $request->validate([

            'username' => 'required',

            'password' => 'required'

        ]);
        // lấy thông tin user theo user name
        $user = User::where('username', $request->username)->first();
        // hàm kiểm tra tồn tai của bản ghi
        // nếu không tồn tại bản ghi hoặc pass vào mail không đúng trả về 401
        if (!$user || !Hash::check($request->password, $user->password)) {

            return response()->json([
                'message' => 'These credentials do not match our records.'
            ], 401);
        }
        // tạo token mới để xác nhận rằng mình là ng đâng nhập 
        $token = $user->createToken('auth_token')->plainTextToken;
        // trả về dữ liệu json
        return response()->json([

            'message' => 'Login success',

            'token' => $token,

            'user' => $user

        ]);
    }

    /**
     * Logout
     */
    public function destroy(Request $request)
    {
        // xóa token sau khi người dùng đăng xuất
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout success'
        ]);
    }
}
