<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('manageProducts')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('manageProducts.index');
    Route::get('/create', [ProductController::class, 'create'])->name('manageProducts.create');
    Route::post('/store', [ProductController::class, 'store'])->name('manageProducts.store');
    Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('manageProducts.edit');
    Route::put('/update', [ProductController::class, 'update'])->name('manageProducts.update');
    Route::delete('/destroy', [ProductController::class, 'destroy'])->name('manageProducts.destroy');
});
