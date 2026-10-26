<?php

use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin/orders')->name('admin.orders.')->controller(OrderController::class)->group(function () {
    Route::get('/pending', 'index')->defaults('status', 'pending')->name('pending');
    Route::get('/delivered', 'index')->defaults('status', 'delivered')->name('delivered');
    Route::get('/cancelled', 'index')->defaults('status', 'cancelled')->name('cancelled');
    Route::get('/pending-summary', 'pendingSummary')->name('pendingSummary');
    Route::get('/{orderId}', 'show')->whereNumber('orderId')->name('details');
    Route::get('/{orderId}/details', 'show')->whereNumber('orderId')->name('details.old');
    Route::post('/{orderId}/update-status', 'updateStatus')->whereNumber('orderId')->name('updateStatus');
    Route::delete('/{orderId}', 'destroy')->whereNumber('orderId')->name('delete');
});
