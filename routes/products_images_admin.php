<?php

use App\Http\Controllers\Admin\ProductImagesController;
use Illuminate\Support\Facades\Route;

Route::delete('/productImage/{id}', [ProductImagesController::class, 'destroy'])->whereNumber('id')->name('productImage.destroy')->middleware('auth');
Route::post('/manageProducts/{productId}/images/order', [ProductImagesController::class, 'reorder'])->whereNumber('productId')->name('productImage.reorder')->middleware('auth');
