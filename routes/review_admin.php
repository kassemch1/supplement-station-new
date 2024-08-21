<?php

use App\Http\Controllers\admin_controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageProductReview')->group(function () {
    Route::get('/{product_id}', [ProductController::class, 'show'])->name('manageProductReview.show');
    Route::delete('/{id}', [ProductController::class, 'destroyReview'])->name('manageProductReview.destroyReview');

});
