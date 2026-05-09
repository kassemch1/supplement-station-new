<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin_controllers\SpecialOfferController;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/special-offers', [SpecialOfferController::class, 'index'])->name('specialOffers.index');
    Route::post('/special-offers', [SpecialOfferController::class, 'store'])->name('specialOffers.store');
    Route::put('/special-offers/{id}', [SpecialOfferController::class, 'update'])->name('specialOffers.update');
    Route::delete('/special-offers/{id}', [SpecialOfferController::class, 'destroy'])->name('specialOffers.destroy');
});
