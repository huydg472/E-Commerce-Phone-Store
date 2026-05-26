<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthTestController extends Controller
{
    // mở giao diện đăng ký
    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        // các ràng buộc validate
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:225', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',

            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',

            'username.required' => 'Vui lòng nhập username.',
            'username.unique' => 'Username đã tồn tại.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);
        // người dùng đăng ký thì cho role là customer
        $customerRole = Role::where('name', 'customer')->firstOrFail();
        // tạo user
        $user = User::create([
            'role_id' => $customerRole->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'username' => $data['username'],
            'password' => $data['password'],
            'status' => 'active',
        ]);

        Auth::login($user);

        $request->session()->regenerate();
        // đăng ký xong đăng nhập luôn
        return redirect()
            ->route('dashboard')
            ->with('success', 'Đăng ký thành công.');
    }
    // hàm show giao diện admin
    public function showLogin()
    {
        return view('login');
    }
    // hàm login
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Vui lòng nhập username.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $remember = $request->boolean('remember');
        // kiểm tra username, pass có trùng không
        // status có active không
        $loginSuccess = Auth::attempt([
            'username' => $data['username'],
            'password' => $data['password'],
            'status' => 'active',
        ], $remember);
        //check pass và user name
        if (!$loginSuccess) {
            return back()
                ->withErrors([
                    'username' => 'Username hoặc mật khẩu không đúng.',
                ])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        // nếu đăng nhập thành công
        return redirect()
            ->route('auth-test.dashboard')
            ->with('success', 'Đăng nhập thành công.');
    }
    // trả về giao diện dashboard
    public function dashboard()
    {
        return view('dashboard');
    }

    public function logout(Request $request)
    {
        // logic logout

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Đăng xuất thành công.');
    }
}
