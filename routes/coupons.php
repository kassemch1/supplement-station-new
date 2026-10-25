<?php

use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Store\CouponController;
use Illuminate\Support\Facades\Route;

// The coupon box on the cart and checkout pages
Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply')->middleware('throttle:10,1,coupon');
Route::post('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');
Route::get('/coupon/applied', [CouponController::class, 'applied'])->name('coupon.applied');

Route::middleware('auth')->prefix('admin/coupons')->name('admin.coupons.')->controller(AdminCouponController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::put('/{id}', 'update')->whereNumber('id')->name('update');
    Route::delete('/{id}', 'destroy')->whereNumber('id')->name('destroy');
    Route::patch('/{id}/toggle-status', 'toggleStatus')->whereNumber('id')->name('toggle-status');
});
