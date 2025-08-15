<?php



use App\Http\Controllers\admin_controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/admin/orders/pending', [OrderController::class, 'pendingOrders'])->name('admin.orders.pending');
    Route::get('/admin/orders/delivered', [OrderController::class, 'deliveredOrders'])->name('admin.orders.delivered');
    Route::get('/admin/orders/{orderId}', [OrderController::class, 'orderDetails'])->name('admin.orders.details');
    Route::post('/admin/orders/{orderId}/update-status', [OrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
    Route::delete('/admin/orders/{orderId}', [OrderController::class, 'deleteOrder'])->name('admin.orders.delete');
    Route::get('/admin/orders/{id}/details', [OrderController::class, 'details'])->name('admin.orders.details');
});
