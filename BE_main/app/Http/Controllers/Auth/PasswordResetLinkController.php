<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Send reset password email
     */
    public function store(Request $request)
    {
        // validate email
        $request->validate([

            'email' => 'required|email'

        ]);
        // kiểm tra tồn tại trong DB 
        // tạo token
        // lưu lại token này vào DB
        $status = Password::sendResetLink(

            $request->only('email')

        );
        // kiểm tra gửi thành công trả về json
        if ($status == Password::RESET_LINK_SENT) {

            return response()->json([

                'message' => __($status)

            ]);
        }
        // nếu thất bại
        return response()->json([

            'message' => __($status)

        ], 400);
    }
}
