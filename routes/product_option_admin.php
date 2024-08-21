<?php

use App\Http\Controllers\admin_controllers\ProductOptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageProductsOptions')->group(function () {
    Route::get('/', [ProductOptionController::class, 'index'])->name('manageProductsOptions.index');
    Route::get('/create/{product_id}', [ProductOptionController::class, 'create'])->name('manageProductsOptions.create');
    Route::post('/store', [ProductOptionController::class, 'store'])->name('manageProductsOptions.store');
    Route::get('/{id}/edit', [ProductOptionController::class, 'edit'])->name('manageProductsOptions.edit');
    Route::put('/{id}', [ProductOptionController::class, 'update'])->name('manageProductOptions.update');
    Route::get('/{product_id}', [ProductOptionController::class, 'show'])->name('manageProductOptions.show');
    Route::delete('/{id}', [ProductOptionController::class, 'destroy'])->name('manageProductOptions.destroy');
});

