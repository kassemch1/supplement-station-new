<?php

use App\Http\Controllers\Admin\ReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageProductReview')->name('manageProductReview.')->controller(ReviewController::class)->group(function () {
    Route::get('/{product_id}', 'index')->whereNumber('product_id')->name('show');
    Route::delete('/{id}', 'destroy')->whereNumber('id')->name('destroyReview');
});
