<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user_controllers\CouponController as UserCouponController;
use App\Http\Controllers\admin_controllers\CouponController as AdminCouponController;

// User routes for coupon application

    Route::post('/coupon/apply', [UserCouponController::class, 'apply'])->name('coupon.apply');
    Route::post('/coupon/remove', [UserCouponController::class, 'remove'])->name('coupon.remove');
    Route::get('/coupon/applied', [UserCouponController::class, 'getAppliedCoupon'])->name('coupon.applied');


// Admin routes for coupon management
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::get('/coupons/create', [AdminCouponController::class, 'create'])->name('coupons.create');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::get('/coupons/{id}/edit', [AdminCouponController::class, 'edit'])->name('coupons.edit');
    Route::put('/coupons/{id}', [AdminCouponController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{id}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');
    Route::patch('/coupons/{id}/toggle-status', [AdminCouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
});
