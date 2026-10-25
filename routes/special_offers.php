<?php

use App\Http\Controllers\Admin\SpecialOfferController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin/special-offers')->name('admin.specialOffers.')->controller(SpecialOfferController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::put('/{id}', 'update')->whereNumber('id')->name('update');
    Route::delete('/{id}', 'destroy')->whereNumber('id')->name('destroy');
});
