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
use App\Http\Controllers\user_controllers\SitemapController;
use App\Http\Middleware\GuestSession;



Route::middleware([GuestSession::class, 'throttle:60,1,cart'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
});
Route::get('/', [HomeController::class, 'index'])->name('home');
// The cart page; the old mini-cart script asked this address for JSON, so JSON requests still get it
Route::get('/cart', [CartController::class, 'Cart'])->name('cart');
Route::get('/checkout', [CheckoutController::class, 'Checkout'])->name('checkout');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/shop/{category}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/products/{product}', [UserProductController::class, 'show'])->name('products.show');
Route::post('/product/{id}/review', [UserProductController::class, 'createReview'])->name('product.createReview')->middleware('throttle:5,1,review');
Route::get('/api/recent-purchases', [RecentPurchaseController::class, 'index'])->name('api.recentPurchases');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Old addresses (mixed case) stay valid for links already shared or indexed: a permanent redirect
// tells search engines to move them to the new address
Route::get('/viewCart', fn () => redirect()->route('cart', [], 301));
Route::get('/Checkout', fn () => redirect()->route('checkout', [], 301));
Route::get('/Shop/search', fn (Illuminate\Http\Request $request) => redirect()->route('shop', array_filter(['shop-search' => $request->query('search')]), 301))->name('home.search');
Route::get('/Shop', [ShopController::class, 'index']);  // redirects to /shop or the category page
