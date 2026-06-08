<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthTestController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', function () {
    return redirect()->route('auth-test.login');
});

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





// test api brands
Route::view('/api-test/brands', 'api_test.brands')
    ->name('api-test.brands');
// test api categories
Route::get('/api-test/categories/{slug?}', function (?string $slug = null) {
    return view('api_test.categories', [
        'initialSlug' => $slug
    ]);
})->name('api-test.categories');
// test api products
Route::get('/api-test/products/{slug?}', function (?string $slug = null) {
    return view('api_test.products', [
        'initialSlug' => $slug
    ]);
})->name('api-test.products');
// test api product-variants
Route::get('/api-test/product-variants/{sku?}', function (?string $sku = null) {
    return view('api_test.product_variants', [
        'initialSku' => $sku
    ]);
})->name('api-test.product-variants');
// test api product-variant-images
Route::view('/api-test/product-variant-images', 'api_test.product_variant_images')
    ->name('api-test.product-variant-images');
// test api product-specification
Route::view('/api-test/product-specifications', 'api_test.product_specifications')
    ->name('api-test.product-specifications');
// test api cart
Route::view('/api-test/cart', 'api_test.cart')
    ->name('api-test.cart');
require __DIR__ . '/auth.php';
