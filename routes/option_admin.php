<?php

use App\Http\Controllers\admin_controllers\OptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manageOptions')->group(function () {
    Route::get('/', [OptionController::class, 'index'])->name('manageOptions.index');
    Route::get('/create', [OptionController::class, 'create'])->name('manageOptions.create');
    Route::post('/store', [OptionController::class, 'store'])->name('manageOptions.store');
    Route::get('/{id}/edit', [OptionController::class, 'edit'])->name('manageOptions.edit');
    Route::put('/update', [OptionController::class, 'update'])->name('manageOptions.update');
    Route::delete('/destroy', [OptionController::class, 'destroy'])->name('manageOptions.destroy');
});
