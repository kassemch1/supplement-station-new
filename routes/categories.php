<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('manageCategories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('manageCategories.index');
    Route::get('/create', [CategoryController::class, 'create'])->name('manageCategories.create');
    Route::post('/store', [CategoryController::class, 'store'])->name('manageCategories.store');
    Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('manageCategories.edit');
    Route::put('/update', [CategoryController::class, 'update'])->name('manageCategories.update');
    Route::delete('/destroy', [CategoryController::class, 'destroy'])->name('manageCategories.destroy');
});
