<?php

use App\Http\Controllers\Admin\ProductEmailController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Store\SubscriptionController;
use Illuminate\Support\Facades\Route;

// The newsletter pop-up
Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe')->middleware('throttle:5,1,subscribe');
Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
Route::get('/subscription/check-modal', [SubscriptionController::class, 'shouldShowModal'])->name('subscription.check.modal');

// The unsubscribe link in every newsletter (signed, see Subscription::unsubscribeUrl). The POST also
// takes email apps' one-click requests, which carry no CSRF token (see bootstrap/app.php): the
// signature protects it instead.
Route::middleware('signed:relative')->group(function () {
    Route::get('/newsletter/unsubscribe/{subscription}', [SubscriptionController::class, 'unsubscribePage'])->name('newsletter.unsubscribe');
    Route::post('/newsletter/unsubscribe/{subscription}', [SubscriptionController::class, 'unsubscribe'])->name('newsletter.unsubscribe.confirm');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('subscribers')->name('subscribers.')->controller(SubscriberController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/export', 'export')->name('export');
        Route::post('/bulk-action', 'bulkAction')->name('bulk-action');
        Route::get('/{subscriber}/edit', 'edit')->name('edit');
        Route::put('/{subscriber}', 'update')->name('update');
        Route::delete('/{subscriber}', 'destroy')->name('destroy');
        Route::post('/{subscriber}/toggle-status', 'toggleStatus')->name('toggle-status');
    });

    // Emailing a product to the subscribers
    Route::post('/products/send-email', [ProductEmailController::class, 'sendToSubscribers'])->name('products.send-email');
    Route::get('/products/{productId}/email-preview', [ProductEmailController::class, 'previewEmail'])->whereNumber('productId')->name('products.email-preview');
});
