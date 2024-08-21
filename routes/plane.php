<?php


use App\Http\Controllers\admin_controllers\PlaneController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('managePlan')->group(function () {
    Route::get('/', [PlaneController::class, 'index'])->name('managePlan.index');
    Route::get('/create', [PlaneController::class, 'create'])->name('managePlan.create');
    Route::post('/store', [PlaneController::class, 'store'])->name('managePlan.store');
    Route::get('/{id}/edit', [PlaneController::class, 'edit'])->name('managePlan.edit');
    Route::put('/update', [PlaneController::class, 'update'])->name('managePlan.update');
    Route::delete('/destroy', [PlaneController::class, 'destroy'])->name('managePlan.destroy');
});
