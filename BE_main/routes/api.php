<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/settings.php';

Route::middleware('site.maintenance')->group(function () {
    require __DIR__ . '/api/catalog.php';
    require __DIR__ . '/api/cart.php';
    require __DIR__ . '/api/contact.php';
    require __DIR__ . '/api/orders.php';
    require __DIR__ . '/api/payments.php';
});

require __DIR__ . '/api/admin.php';
