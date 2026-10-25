<?php

use App\Http\Controllers\Admin\OptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageOptions')->name('manageOptions.')->controller(OptionController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::put('/update', 'update')->name('update');
    Route::delete('/destroy', 'destroy')->name('destroy');
});
