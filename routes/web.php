<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/products.php';
require __DIR__.'/categories.php';
require __DIR__.'/admin.php';
require __DIR__.'/cart.php';
require __DIR__.'/banner.php';
require __DIR__.'/products_images_admin.php';
require __DIR__.'/plane.php';
require __DIR__. '/faq.php';
require __DIR__. '/option_admin.php';
require __DIR__. '/product_option_admin.php';
require __DIR__. '/order_admin.php';
require __DIR__. '/contact.php';
require __DIR__. '/review_admin.php';
require __DIR__. '/coupons.php';
require __DIR__. '/subscribe.php';
require __DIR__. '/special_offers.php';

use App\Http\Controllers\user_controllers\HomeController;
use App\Http\Controllers\user_controllers\RecentPurchaseController;
use App\Http\Controllers\user_controllers\UserProductController;
use App\Http\Controllers\user_controllers\CartController;
use App\Http\Controllers\user_controllers\CheckoutController;
use App\Http\Controllers\user_controllers\ShopController;
use App\Http\Middleware\GuestSession;



Route::middleware([GuestSession::class, 'throttle:60,1,cart'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
});
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/viewCart', [CartController::class, 'Cart'])->name('cart');
Route::get('/Checkout', [CheckoutController::class, 'Checkout'])->name('checkout');
Route::get('/Shop',[ShopController::class,'index'])->name('shop');
Route::get('/products/{id}', [UserProductController::class, 'show'])->name('products.show');
Route::post('/product/{id}/review', [UserProductController::class, 'createReview'])->name('product.createReview')->middleware('throttle:5,1,review');
Route::get('/api/recent-purchases', [RecentPurchaseController::class, 'index'])->name('api.recentPurchases');

// These two URLs pointed at controller methods that no longer exist and returned a server error.
// Send anyone following an old link to the working shop page instead.
Route::get('/shop/{categoryName?}', fn (?string $categoryName = null) => redirect()->route('shop', array_filter(['category' => $categoryName]), 301))->name('shop.category');
Route::get('/Shop/search', fn (Illuminate\Http\Request $request) => redirect()->route('shop', array_filter(['shop-search' => $request->query('search')]), 301))->name('home.search');
