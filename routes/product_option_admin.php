<?php

use App\Http\Controllers\Admin\ProductOptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageProductsOptions')->controller(ProductOptionController::class)->group(function () {
    Route::get('/', 'index')->name('manageProductsOptions.index');
    Route::get('/create/{product_id}', 'create')->whereNumber('product_id')->name('manageProductsOptions.create');
    Route::post('/store', 'store')->name('manageProductsOptions.store');
    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('manageProductsOptions.edit');
    Route::put('/{id}', 'update')->whereNumber('id')->name('manageProductOptions.update');
    Route::get('/{product_id}', 'show')->whereNumber('product_id')->name('manageProductOptions.show');
    Route::delete('/{id}', 'destroy')->whereNumber('id')->name('manageProductOptions.destroy');
    Route::delete('/{id}/image', 'removeImage')->whereNumber('id')->name('manageProductOptions.removeImage');
});
