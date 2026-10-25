<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\Review;
use App\Models\SpecialOffer;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_page_opens_with_the_shared_layout(): void
    {
        $product = Product::factory()->withImage()->withOptions('Flavor', ['Vanilla' => true])->create();
        $order = Order::factory()->placed()->create();
        Review::factory()->create(['product_id' => $product->id]);
        Banner::factory()->create(['product_id' => $product->id]);
        SpecialOffer::factory()->create(['product_id' => $product->id]);
        $coupon = Coupon::factory()->create();
        $subscriber = Subscription::factory()->create();
        $faq = Faq::factory()->create();
        $plan = Plan::factory()->create();
        $productOption = ProductOption::first();

        $pages = [
            '/admin', '/admin/coupons', '/admin/coupons/create', "/admin/coupons/{$coupon->id}/edit",
            '/admin/orders/pending', '/admin/orders/delivered', '/admin/orders/cancelled', "/admin/orders/{$order->id}", "/admin/orders/{$order->id}/details",
            "/admin/products/{$product->id}/email-preview", '/admin/special-offers',
            '/admin/subscribers', '/admin/subscribers/create', "/admin/subscribers/{$subscriber->id}/edit",
            '/manageBanner', '/manageBanner/create', '/manageCategories', '/manageCategories/create', "/manageCategories/{$product->category_id}/edit",
            '/manageFaq', '/manageFaq/create', "/manageFaq/{$faq->id}/edit", '/manageOptions', '/manageOptions/create', "/manageOptions/{$productOption->option_id}/edit",
            '/managePlan', '/managePlan/create', "/managePlan/{$plan->id}/edit", "/manageProductReview/{$product->id}",
            '/manageProducts', '/manageProducts?stock=in-stock', '/manageProducts?stock=archived', '/manageProducts/create', "/manageProducts/{$product->id}/edit",
            '/manageProductsOptions', "/manageProductsOptions/create/{$product->id}", "/manageProductsOptions/{$productOption->id}/edit", "/manageProductsOptions/{$product->id}",
        ];

        $this->admin();
        foreach ($pages as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            if (str_contains($url, 'email-preview')) {
                continue;
            }
            $this->assertSame(1, substr_count($html, 'jquery-3.3.1.min.js'), "$url loads jQuery once");
            $this->assertStringNotContainsString('jquery-3.6.0', $html, $url);
            $this->assertStringNotContainsString('plugins/plugins.js', $html, $url);
            $this->assertSame(1, substr_count($html, 'admin_assets/css/style'), "$url loads the theme stylesheet once");
            $this->assertStringContainsString('<meta name="csrf-token"', $html, $url);
            $this->assertStringContainsString('class="content-body"', $html, $url);
            $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html, $url);
        }
    }

    public function test_missing_records_are_404_not_errors(): void
    {
        $this->admin();
        foreach (['/manageProducts/999/edit', '/manageCategories/999/edit', '/manageFaq/999/edit', '/manageOptions/999/edit',
            '/managePlan/999/edit', '/manageProductsOptions/999/edit', '/admin/orders/999', '/admin/coupons/999/edit',
            '/admin/products/999/email-preview', '/admin/orders/abc'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }

    public function test_admin_pages_need_a_login(): void
    {
        foreach (['/admin', '/manageProducts', '/admin/orders/pending', '/admin/subscribers/export'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->postJson('/manageCategories/store', ['name' => 'x'])->assertUnauthorized();
        $this->deleteJson('/manageProducts/destroy', ['id' => 1])->assertUnauthorized();
    }

    public function test_login_logout_and_throttling(): void
    {
        $user = User::factory()->create(['email' => 'boss@example.com', 'password' => 'right-password']);

        $this->post('/auth', ['email' => 'boss@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/auth', ['email' => 'boss@example.com', 'password' => 'right-password'])->assertRedirect('/manageProducts/create');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/auth', ['email' => 'boss@example.com', 'password' => 'wrong']);
        }
        $this->post('/auth', ['email' => 'boss@example.com', 'password' => 'right-password'])
            ->assertSessionHasErrors(['email' => 'Too many login attempts. Please try again in 15 minute(s).']);
        $this->assertGuest();
    }
}
