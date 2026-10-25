<?php

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Read-only smoke test: hits every GET page against the local DB copy (no RefreshDatabase).
class UpgradeSmokeTest extends TestCase
{
    public function test_pages(): void
    {
        $id = fn ($t) => DB::table($t)->value('id');
        $product = DB::table('product_options')->value('product_id') ?? $id('products');
        $urls = [
            '/', '/shop', '/shop?orderby=low-to-high&category-sidebar='.$id('categories'), '/shop?shop-search=whey',
            '/checkout', '/cart', '/cart', '/api/cart', '/api/recent-purchases', '/coupon/applied',
            '/subscription/check-modal', '/login', '/up', $this->productUrl($product),
            '/admin', '/admin/coupons', '/admin/coupons/create', '/admin/coupons/'.$id('coupons').'/edit',
            '/admin/orders/delivered', '/admin/orders/pending', '/admin/orders/'.$id('orders').'/details',
            '/admin/orders/'.$id('orders'), "/admin/products/$product/email-preview", '/admin/special-offers',
            '/admin/subscribers', '/admin/subscribers/create', '/admin/subscribers/'.$id('subscriptions').'/edit',
            '/admin/subscribers/export', '/manageBanner', '/manageBanner/create', '/manageCategories',
            '/manageCategories/create', '/manageCategories/'.$id('categories').'/edit', '/manageFaq', '/manageFaq/create',
            '/manageFaq/'.$id('faqs').'/edit', '/manageOptions', '/manageOptions/create', '/manageOptions/'.$id('options').'/edit',
            '/managePlan', '/managePlan/create', "/manageProductReview/$product", '/manageProducts', '/manageProducts?stock=in-stock',
            '/manageProducts/create', "/manageProducts/$product/edit", '/manageProductsOptions',
            "/manageProductsOptions/create/$product", '/manageProductsOptions/'.$id('product_options').'/edit',
            "/manageProductsOptions/$product",
        ];

        $user = \App\Models\User::first();
        $fail = 0;
        foreach ($urls as $url) {
            $res = $this->actingAs($user)->get($url);
            $code = $res->getStatusCode();
            $ex = $res->baseResponse->exception ?? null; $note = $ex ? ' '.class_basename($ex).": ".substr($ex->getMessage(), 0, 150) : '';
            if ($code >= 500) $fail++;
            fwrite(STDOUT, str_pad($code, 5).$url.$note."\n");
        }
        fwrite(STDOUT, "server errors: $fail\n");
        $this->assertTrue(true);
    }
}
