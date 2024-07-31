<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/products.php';
require __DIR__.'/categories.php';
require __DIR__.'/admin.php';

use App\Http\Controllers\user_controllers\HomeController;
use App\Http\Controllers\user_controllers\SingleProductController;
use App\Http\Controllers\user_controllers\CartController;
use App\Http\Controllers\user_controllers\CheckoutController;

use App\Http\Middleware\GuestSession;

Route::middleware([GuestSession::class])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/singleProduct', [SingleProductController::class, 'singleProduct'])->name('singleProduct');
    Route::get('/Cart', [CartController::class, 'Cart'])->name('cart');
    Route::get('/Checkout', [CheckoutController::class, 'Checkout'])->name('checkout');
});




// For web routes
Route::get('/cart', [CartController::class, 'getCart'])->name('cart.get');

Route::get('/api/cart', [CartController::class, 'getCart'])->name('api.cart.get');
