<?php


use App\Http\Controllers\admin_controllers\BannerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('manageBanner', [BannerController::class, 'index'])->name('manageBanner.index');
    Route::get('/manageBanner/create', [BannerController::class, 'create'])->name('manageBanner.create');
    Route::post('/manageBanner/store', [BannerController::class, 'store'])->name('manageBanner.store');
    // Uncomment if needed:
    // Route::get('/manageBanner/{id}/edit', [BannerController::class, 'edit'])->name('manageBanner.edit');
    // Route::put('/manageBanner/update', [BannerController::class, 'update'])->name('manageBanner.update');
    // Route::delete('/manageBanner/destroy', [BannerController::class, 'destroy'])->name('manageBanner.destroy');
});
