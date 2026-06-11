<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthTestController;
use App\Http\Controllers\PaymentController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', function () {
    return redirect()->route('auth-test.login');
});

Route::get('/payments/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payments.vnpay.return');

Route::prefix('auth-test')->name('auth-test.')->group(function () {
    Route::middleware('guest')->group(function () { // người chưa đăng nhập, đăng ký mới có thể vào form
        Route::get('/register', [AuthTestController::class, 'showRegister'])->name('register'); // gọi đến form đăng ký
        Route::post('/register', [AuthTestController::class, 'register'])->name('register.post'); // goi đến hàm xử lý đăng ký

        Route::get('/login', [AuthTestController::class, 'showLogin'])->name('login'); // gọi đến form đăng nhập
        Route::post('/login', [AuthTestController::class, 'login'])->name('login.post'); // gọi đến hàm xử lý đăng nhập
    });

    Route::middleware('auth')->group(function () { // người đã đăng nhập mới được vào dashboard và đăng xuất
        Route::get('/dashboard', [AuthTestController::class, 'dashboard'])->name('dashboard'); //trang dashboard khi đăng nhập
        Route::post('/logout', [AuthTestController::class, 'logout'])->name('logout'); // hàm xử lý đăng xuất
    });
});





require __DIR__ . '/auth.php';
