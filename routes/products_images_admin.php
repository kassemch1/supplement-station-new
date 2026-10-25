<?php

use App\Http\Controllers\Admin\ProductImagesController;
use Illuminate\Support\Facades\Route;

Route::delete('/productImage/{id}', [ProductImagesController::class, 'destroy'])->whereNumber('id')->name('productImage.destroy')->middleware('auth');
