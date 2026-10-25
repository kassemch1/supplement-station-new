<?php

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageProducts')->name('manageProducts.')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::post('/update', 'update')->name('update');
    Route::delete('/destroy', 'destroy')->name('destroy');
    Route::post('/{id}/restore', 'restore')->whereNumber('id')->name('restore');
});
