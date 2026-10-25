<?php

use App\Http\Controllers\Admin\BrandController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageBrands')->name('manageBrands.')->controller(BrandController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::post('/{id}', 'update')->whereNumber('id')->name('update');
    Route::delete('/{id}', 'destroy')->whereNumber('id')->name('destroy');
});
