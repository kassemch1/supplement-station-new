<?php

use App\Http\Controllers\Admin\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('managePlan')->name('managePlan.')->controller(PlanController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::put('/update', 'update')->name('update');
    Route::delete('/destroy', 'destroy')->name('destroy');
});
