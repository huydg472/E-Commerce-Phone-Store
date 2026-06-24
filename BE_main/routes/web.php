<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/login', '/auth/login');
Route::redirect('/register', '/auth/register');

Route::get('/payments/vnpay/return', [PaymentController::class, 'vnpayReturn'])
    ->name('payments.vnpay.return');

require __DIR__ . '/auth.php';
