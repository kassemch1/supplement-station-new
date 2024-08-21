<?php


use App\Http\Controllers\admin_controllers\BannerController;
use App\Http\Controllers\user_controllers\ContactController;
use Illuminate\Support\Facades\Route;





Route::post('/contact/send',[ContactController::class,'sendEmailContact'])->name('contact.send');
