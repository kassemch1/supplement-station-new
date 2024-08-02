<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/products.php';
require __DIR__.'/categories.php';
require __DIR__.'/admin.php';
require __DIR__.'/cart.php';
require __DIR__.'/banner.php';
require __DIR__.'/products_images_admin.php';
require __DIR__. '/faq.php';

use App\Http\Controllers\user_controllers\HomeController;
use App\Http\Controllers\user_controllers\UserProductController;
use App\Http\Controllers\user_controllers\CartController;
use App\Http\Controllers\user_controllers\CheckoutController;
use App\Http\Controllers\user_controllers\ShopController;
use App\Http\Middleware\GuestSession;
use App\Http\Controllers\user_controllers\UserCategoryController;

Route::middleware([GuestSession::class])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/Cart', [CartController::class, 'Cart'])->name('cart');
    Route::get('/Checkout', [CheckoutController::class, 'Checkout'])->name('checkout');
    Route::get('/Shop',[ShopController::class,'Shop'])->name('shop');
    Route::get('/products/{id}', [UserProductController::class, 'show'])->name('products.show');
    Route::get('/shop/{categoryName?}', [ShopController::class, 'Shop'])->name('shop.category');

    
});


