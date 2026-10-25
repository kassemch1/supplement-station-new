<?php

use App\Http\Controllers\Store\ContactController;
use Illuminate\Support\Facades\Route;

Route::post('/contact/send', [ContactController::class, 'send'])->name('contact.send')->middleware('throttle:3,1,contact');
