<?php

use App\Http\Controllers\admin_controllers\ProductImagesController;
use Illuminate\Support\Facades\Route;

Route::delete('/productImage/{id}',[ProductImagesController::class,'destroy'])->name('productImage.destroy')->middleware('auth');




