<?php

use App\Http\Controllers\admin_controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user_controllers\HomeController;
use App\Http\Controllers\user_controllers\SingleProductController;
use App\Http\Controllers\user_controllers\CartController;
use App\Http\Controllers\user_controllers\CheckoutController;
use App\Http\Controllers\admin_controllers\BannerController;
use App\Http\Controllers\user_controllers\ShopController;

Route::get('/',[HomeController::class,'index'])->name('home');
Route::get('/singleProduct',[SingleProductController::class,'singleProduct'])->name('singleProduct');
Route::get('/Cart',[CartController::class,'Cart'])->name('cart');
Route::get('/Checkout',[CheckoutController::class,'Checkout'])->name('checkout');
Route::get('/Shop',[ShopController::class,'Shop'])->name('shop');
//admin login page
Route::get('/login',[AdminController::class,'loginView'])->name('login');
//logout
Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
//auth
Route::post('/auth',[AdminController::class,'authenticate'])->name('auth');


//admin dashboard
Route::get('/admin',[AdminController::class,'admin_dashboard'])->name('admin');
//display all categories in a table
Route::get('manageCategories',[CategoryController::class,'index'])->name('manageCategories.index');
//display form to add a new category
Route::get('/manageCategories/create',[CategoryController::class,'create'])->name('manageCategories.create');
//store a new category
Route::post('/manageCategories/store',[CategoryController::class,'store'])->name('manageCategories.store');
//display form to edit a category
Route::get('/manageCategories/{id}/edit',[CategoryController::class,'edit'])->name('manageCategories.edit');
//update a category
Route::put('/manageCategories/update',[CategoryController::class,'update'])->name('manageCategories.update');
//delete a category
Route::delete('/manageCategories/destroy',[CategoryController::class,'destroy'])->name('manageCategories.destroy');


Route::get('manageProducts',[ProductController::class,'index'])->name('manageProducts.index');
//display form to add a new product
Route::get('/manageProducts/create',[ProductController::class,'create'])->name('manageProducts.create');
//store a new product
Route::post('/manageProducts/store',[ProductController::class,'store'])->name('manageProducts.store');
//display form to edit a product
Route::get('/manageProducts/{id}/edit',[ProductController::class,'edit'])->name('manageProducts.edit');
//update a product
Route::put('/manageProducts/update',[ProductController::class,'update'])->name('manageProducts.update');
//delete a product
Route::delete('/manageProducts/destroy',[ProductController::class,'destroy'])->name('manageProducts.destroy');

Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show');

Route::get('/', [ProductController::class, 'getall'])->name('products.getall');

Route::get('manageBanner',[BannerController::class,'index'])->name('manageBanner.index');
//display form to add a new banner
Route::get('/manageBanner/create',[BannerController::class,'create'])->name('manageBanner.create');
//store a new banner
Route::post('/manageBanner/store',[BannerController::class,'store'])->name('manageBanner.store');
//display form to edit a banner
//Route::get('/manageBanner/{id}/edit',[BannerController::class,'edit'])->name('manageBanner.edit');
//update a banner
//Route::put('/manageBanner/update',[BannerController::class,'update'])->name('manageBanner.update');
//delete a banner
//Route::delete('/manageBanner/destroy',[BannerController::class,'destroy'])->name('manageBanner.destroy');
