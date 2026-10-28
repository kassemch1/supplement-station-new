<?php

use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\RecentPurchaseController;
use App\Http\Controllers\Store\ShopController;
use App\Http\Controllers\Store\SearchController;
use App\Http\Controllers\Store\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Each feature has its own route file
require __DIR__ . '/products.php';
require __DIR__ . '/categories.php';
require __DIR__ . '/brands.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/cart.php';
require __DIR__ . '/banner.php';
require __DIR__ . '/products_images_admin.php';
require __DIR__ . '/plane.php';
require __DIR__ . '/faq.php';
require __DIR__ . '/option_admin.php';
require __DIR__ . '/product_option_admin.php';
require __DIR__ . '/order_admin.php';
require __DIR__ . '/customers_admin.php';
require __DIR__ . '/contact.php';
require __DIR__ . '/review_admin.php';
require __DIR__ . '/coupons.php';
require __DIR__ . '/subscribe.php';
require __DIR__ . '/special_offers.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/seo.php';

// ---------- store pages ----------

// The shop is the front page; the old /shop address sends visitors there (see ShopController::index)
Route::get('/', [ShopController::class, 'index'])->name('shop');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index']);
Route::get('/shop/{category}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{id}/quick', [ProductController::class, 'quick'])->whereNumber('id')->name('products.quick');
Route::post('/product/{id}/review', [ProductController::class, 'createReview'])->whereNumber('id')
    ->name('product.createReview')->middleware('throttle:5,1,review');
Route::get('/api/recent-purchases', [RecentPurchaseController::class, 'index'])->name('api.recentPurchases');
Route::get('/api/search', [SearchController::class, 'suggest'])->name('api.search')->middleware('throttle:120,1,search');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Old addresses (mixed case) stay valid for links already shared or indexed: a permanent redirect
// tells search engines to move them to the new address
Route::get('/viewCart', fn () => redirect()->route('cart', [], 301));
Route::get('/Checkout', fn () => redirect()->route('checkout', [], 301));
Route::get('/Shop/search', fn (Request $request) => redirect(rtrim(url('/') . '/?' . http_build_query(array_filter(['shop-search' => $request->query('search')])), '?'), 301))->name('home.search');
Route::get('/Shop', [ShopController::class, 'index']);  // redirects to the shop or the category page
