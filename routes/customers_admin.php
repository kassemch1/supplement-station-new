<?php

use App\Http\Controllers\Admin\CustomerController;
use Illuminate\Support\Facades\Route;

// Customers, worked out from the orders (grouped by phone number)
Route::middleware('auth')->prefix('admin/customers')->name('admin.customers.')->controller(CustomerController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{phone}', 'show')->where('phone', '[0-9]{3,20}')->name('show');
});
