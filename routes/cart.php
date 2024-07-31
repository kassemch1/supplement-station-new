<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user_controllers\CartController;

Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/cart', [CartController::class, 'getCart'])->name('cart.get');
Route::get('/api/cart', [CartController::class, 'getCart'])->name('api.cart.get');
Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'removeItem'])->name('cart.remove');
