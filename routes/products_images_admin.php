<?php

use App\Http\Controllers\admin_controllers\ProductImagesController;
use Illuminate\Support\Facades\Route;

Route::get('/productImage/{id}/destroy',[ProductImagesController::class,'destroy'])->name('productImage.destroy');




