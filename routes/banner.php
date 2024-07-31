<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin_controllers\BannerController;





Route::get('manageBanner',[BannerController::class,'index'])->name('manageBanner.index');
//display form to add a new banner
Route::get('/manageBanner/create',[BannerController::class,'create'])->name('manageBanner.create');
//store a new banner
Route::post('/manageBanner/store',[BannerController::class,'store'])->name('manageBanner.store');
//display form to edit a banner
//Route::get('/manageBanner/{id}/edit',[BannerController::class,'edit'])->name('manageBanner.edit');
//update a banner
//Route::put('/manageBanner/update',[BannerController::class,'update'])->name('manageBanner.update');
//delete a banner
//Route::delete('/manageBanner/destroy',[BannerController::class,'destroy'])->name('manageBanner.destroy');
