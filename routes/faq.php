<?php

use App\Http\Controllers\admin_controllers\FaqController;
use Illuminate\Support\Facades\Route;

Route::prefix('manageFaq')->group(function () {
    Route::get('/create',[FaqController::class,'create'])->name('manageFaq.create');
    //store a new faq
    Route::post('/store',[FaqController::class,'store'])->name('manageFaq.store');
    //list all existing faqs in a table
    Route::get('/',[FaqController::class,'index'])->name('manageFaq.index');
    //Display form to edit an existing faq
    Route::get('/{id}/edit',[FaqController::class,'edit'])->name('manageFaq.edit');
    //update an existing FAQ
    Route::put('/update',[FaqController::class,'update'])->name('manageFaq.update');
    //Delete an faq
    Route::delete('/destroy',[FaqController::class,'destroy'])->name('manageFaq.destroy');
});




