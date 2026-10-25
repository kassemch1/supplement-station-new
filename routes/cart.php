<?php

use App\Http\Controllers\Store\CartController;
use Illuminate\Support\Facades\Route;

// The cart page; the old mini-cart script asked this address for JSON, so JSON requests still get it
Route::get('/cart', [CartController::class, 'show'])->name('cart');
Route::view('/checkout', 'user_views/Checkout')->name('checkout');

Route::get('/api/cart', [CartController::class, 'items'])->name('api.cart.get');
Route::get('/api/cart/suggestions', [CartController::class, 'suggestions'])->name('api.cart.suggestions');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add')->middleware('throttle:60,1,cart');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/place-order', [CartController::class, 'placeOrder'])->name('place.order')->middleware('throttle:10,1,order');
