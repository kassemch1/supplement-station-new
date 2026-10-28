<?php

use App\Http\Controllers\Admin\PushController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin/settings')->controller(SettingsController::class)->group(function () {
    Route::get('/', 'edit')->name('admin.settings');
    Route::put('/', 'update')->name('admin.settings.update');
    Route::post('/test-email', 'testEmail')->name('admin.settings.testEmail');
});

// New-order notifications on the admin's phone (App\Services\AdminPush)
Route::middleware('auth')->prefix('admin/push')->name('admin.push.')->controller(PushController::class)->group(function () {
    Route::get('/key', 'key')->name('key');
    Route::post('/subscribe', 'subscribe')->name('subscribe');
    Route::post('/unsubscribe', 'unsubscribe')->name('unsubscribe');
    Route::post('/test', 'test')->name('test');
    Route::delete('/devices/{id}', 'destroy')->whereNumber('id')->name('destroy');
});
