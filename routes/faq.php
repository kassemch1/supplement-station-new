<?php

use App\Http\Controllers\Admin\FaqController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageFaq')->name('manageFaq.')->controller(FaqController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::put('/update', 'update')->name('update');
    Route::delete('/destroy', 'destroy')->name('destroy');
});
