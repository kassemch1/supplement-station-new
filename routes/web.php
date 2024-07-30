<?php

use App\Http\Controllers\admin_controllers\AdminController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user_controllers\HomeController;
use App\Http\Controllers\user_controllers\SingleProductController;
use App\Http\Controllers\user_controllers\CartController;
use App\Http\Controllers\user_controllers\CheckoutController;

Route::get('/',[HomeController::class,'index'])->name('home');
Route::get('/singleProduct',[SingleProductController::class,'singleProduct'])->name('singleProduct');
Route::get('/Cart',[CartController::class,'Cart'])->name('cart');
Route::get('/Checkout',[CheckoutController::class,'Checkout'])->name('checkout');

//admin login page
Route::get('/login',[AdminController::class,'loginView'])->name('login');
//logout
Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
//auth
Route::post('/auth',[AdminController::class,'authenticate'])->name('auth');


//admin dashboard
Route::get('/admin',[AdminController::class,'admin_dashboard'])->name('admin');
