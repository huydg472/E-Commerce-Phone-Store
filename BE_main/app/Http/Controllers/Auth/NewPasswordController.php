<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class NewPasswordController extends Controller
{
    /**
     * Reset password
     */
    public function store(Request $request)
    {
        //check validate
        $request->validate([

            'token' => 'required',

            'email' => 'required|email',

            'password' => 'required|confirmed|min:6'

        ]);
        // so sánh token ng dugnf gửi vào token được lưu vòa DB
        $status = Password::reset(

            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            // sau khi check đúng token goi hàm callback dưới
            function ($user, $password) {

                $user->forceFill([

                    'password' => $password

                ])->save();

                $user->setRememberToken(Str::random(60));
            }

        );
        //kiểm tra lai pass
        if ($status == Password::PASSWORD_RESET) {

            return response()->json([

                'message' => __($status)

            ]);
        }

        return response()->json([

            'message' => __($status)

        ], 400);
    }
}
