<?php

use App\Services\IndexNow;
use Illuminate\Support\Facades\Route;

// IndexNow's proof that the addresses it receives come from this site (see App\Services\IndexNow)
Route::get('/indexnow.txt', function () {
    abort_unless($key = IndexNow::key(), 404);

    return response($key, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('indexnow.key');
