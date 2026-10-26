<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Review;
use App\Models\SpecialOffer;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// admin-ui.js reloads the parts marked data-ajax-list, sends data-ajax-form forms in the background
// and applies data-ajax-filter forms as you type. These checks keep the pages and the script in step.
class AjaxListsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin();
    }

    public function test_every_list_page_marks_what_reloads_in_place(): void
    {
        $product = Product::factory()->withImage()->withOptions('Flavor', ['Vanilla' => true])->create();
        $order = Order::factory()->placed()->create();
        Review::factory()->create(['product_id' => $product->id]);
        Banner::factory()->create(['product_id' => $product->id]);
        SpecialOffer::factory()->create(['product_id' => $product->id]);
        Coupon::factory()->create();
        Subscription::factory()->create();
        Faq::factory()->create();
        Plan::factory()->create();

        $pages = [
            '/manageProducts' => ['products-table', 'products-pages'],
            '/manageCategories' => ['list'], '/manageOptions' => ['list'], '/manageBanner' => ['list'],
            '/managePlan' => ['list'], '/manageFaq' => ['list'], "/manageProductsOptions/{$product->id}" => ['list'],
            "/manageProductReview/{$product->id}" => ['list'],
            '/admin/orders/pending' => ['orders'], "/admin/orders/{$order->id}" => ['order-head', 'order-actions'],
            '/admin/coupons' => ['coupons'], '/admin/subscribers' => ['subscriber-stats', 'subscribers'],
            '/admin/special-offers' => ['offer-add', 'offer-list'], "/manageProducts/{$product->id}/edit" => ['product-images'],
        ];
        foreach ($pages as $url => $lists) {
            $html = $this->get($url)->assertOk()->getContent();
            foreach ($lists as $list) {
                $this->assertStringContainsString('data-ajax-list="' . $list . '"', $html, $url);
            }
            $this->assertStringNotContainsString('location.reload()', $html, "$url refreshes its list instead of the page");
        }

        foreach (['/manageProducts', '/admin/orders/pending', '/admin/coupons', '/admin/subscribers'] as $url) {
            $this->get($url)->assertSee('data-ajax-filter', false);
        }
        $this->get('/admin/coupons')->assertSee('data-ajax-clear', false);
        $this->get('/admin/orders/pending')->assertSee('data-clear-to="desc"', false)->assertDontSee('this.form.submit()', false);
    }

    public function test_the_layout_loads_select2_once_and_pages_do_not_bring_their_own(): void
    {
        // Select2 is part of the admin.js bundle, ahead of the script that turns the dropdowns into it
        $files = config('assets.bundles')['admin.js']['files'];
        $this->assertContains('public/admin_assets/js/plugins/select2/select2.full.min.js', $files);
        $this->assertLessThan(array_search('resources/assets/admin/admin-ui.js', $files), array_search('public/admin_assets/js/plugins/select2/select2.full.min.js', $files));

        foreach (['/admin/special-offers', '/manageProducts/create', '/admin/coupons'] as $url) {
            $html = $this->get($url)->getContent();
            $this->assertSame(1, substr_count($html, 'admin_assets/build/admin.js?v='), $url);
            $this->assertStringNotContainsString('select2.full.min.js', $html, $url);
            $this->assertStringNotContainsString('cdn.jsdelivr.net/npm/select2', $html, $url);
        }
    }

    public function test_dashboard_offers_clear_filters_only_when_filtered(): void
    {
        $this->get('/admin')->assertDontSee('Clear filters');
        $this->get('/admin?range=7d')->assertSee('Clear filters');
    }

    public function test_background_forms_land_back_on_the_list_with_its_message(): void
    {
        $coupon = Coupon::factory()->create(['is_active' => true]);
        $ajax = ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json, text/html;q=0.9'];

        // what admin-ui.js reads: the page it is sent back to, with the success box on it
        $this->withHeaders($ajax)->followingRedirects()->patch("/admin/coupons/{$coupon->id}/toggle-status")
            ->assertOk()->assertSee('data-ajax-list="coupons"', false)->assertSee('Coupon deactivated successfully!');
        $this->assertFalse($coupon->fresh()->is_active);

        // and validation problems come back as JSON for the pop-up, not as a redirect
        $order = Order::factory()->create(['status' => 'pending']);
        $this->withHeaders($ajax)->post("/admin/orders/{$order->id}/update-status", ['status' => 'lost'])
            ->assertStatus(422)->assertJsonValidationErrors('status');
    }
}
