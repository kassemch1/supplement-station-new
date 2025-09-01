<?php


use App\Http\Controllers\admin_controllers\AdminSubscriptionController;
use App\Http\Controllers\admin_controllers\ProductEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user_controllers\SubscriptionController;

Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
Route::get('/subscription/check-modal', [SubscriptionController::class, 'shouldShowModal'])->name('subscription.check.modal');

Route::prefix('admin')->name('admin.')->group(function () {
    // List all subscribers
    Route::get('/subscribers', [AdminSubscriptionController::class, 'index'])->name('subscribers.index');

    // Show create form
    Route::get('/subscribers/create', [AdminSubscriptionController::class, 'create'])->name('subscribers.create');

    // Store new subscriber
    Route::post('/subscribers', [AdminSubscriptionController::class, 'store'])->name('subscribers.store');

    // Show edit form
    Route::get('/subscribers/{subscriber}/edit', [AdminSubscriptionController::class, 'edit'])->name('subscribers.edit');

    // Update subscriber
    Route::put('/subscribers/{subscriber}', [AdminSubscriptionController::class, 'update'])->name('subscribers.update');

    // Delete subscriber
    Route::delete('/subscribers/{subscriber}', [AdminSubscriptionController::class, 'destroy'])->name('subscribers.destroy');

    Route::post('subscribers/{subscriber}/toggle-status', [AdminSubscriptionController::class, 'toggleStatus'])->name('subscribers.toggle-status');
    Route::post('subscribers/bulk-action', [AdminSubscriptionController::class, 'bulkAction'])->name('subscribers.bulk-action');
    Route::get('subscribers/export', [AdminSubscriptionController::class, 'export'])->name('subscribers.export');
    // Product email routes
    Route::post('/products/send-email', [ProductEmailController::class, 'sendToSubscribers'])->name('products.send-email');
    Route::get('/products/{productId}/email-preview', [ProductEmailController::class, 'previewEmail'])->name('products.email-preview');
});
