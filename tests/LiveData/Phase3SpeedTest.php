<?php

use App\Jobs\SendProductNewsletter;
use App\Mail\ContactMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase3SpeedTest extends TestCase
{
    use DatabaseTransactions;

    private const PAGES = ['/', '/shop', '/cart', '/checkout'];

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('store.categories');
    }

    private function productPage(): string
    {
        return $this->productUrl(DB::table('products')->whereNull('deleted_at')->value('id'));
    }

    private function queries(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    // ---------- shared layout and bundles ----------

    public function test_built_css_and_js_match_their_sources(): void
    {
        $this->assertSame(0, Artisan::call('assets:build', ['--check' => true]), Artisan::output());
    }

    public function test_every_store_page_uses_the_layout_and_the_bundles(): void
    {
        foreach ([...self::PAGES, $this->productPage()] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('#assets/build/store\.css\?v=[0-9a-f]{10}#', $html, $url);
            $this->assertMatchesRegularExpression('#<script src="[^"]*assets/build/store\.js\?v=[0-9a-f]{10}" defer>#', $html, $url);
            $this->assertStringContainsString('window.SS = {', $html, $url);
            $this->assertSame(1, substr_count($html, 'id="subscriptionModal"') <= 1 ? 1 : 0, $url);
            foreach (['jquery-3.7.1.min.js', 'jquery-3.6.0', 'jquery-ui', 'magnific', 'inview', 'scrollspy', '<style>'] as $gone) {
                $this->assertStringNotContainsString($gone, $html, "$url still has $gone");
            }
            $this->assertSame(1, substr_count($html, '<header'), "$url renders the header once");
            $this->assertSame(1, substr_count($html, '<footer'), "$url renders the footer once");
        }
    }

    public function test_page_bundles_load_only_on_their_page(): void
    {
        $this->assertStringContainsString('assets/build/shop.js', $this->get('/shop')->getContent());
        $this->assertStringContainsString('assets/build/cart.js', $this->get('/cart')->getContent());
        $this->assertStringContainsString('assets/build/checkout.js', $this->get('/checkout')->getContent());
        $this->assertStringContainsString('assets/build/product.js', $this->get($this->productPage())->getContent());
        $home = $this->get('/')->getContent();
        $this->assertStringContainsString('assets/build/home.css', $home);
        $this->assertStringNotContainsString('assets/build/shop.js', $home);
    }

    // ---------- no server-side phone detection ----------

    public function test_phone_and_desktop_get_the_same_page(): void
    {
        $phone = ['User-Agent' => 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 Mobile Safari/537.36'];
        $desktop = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36'];
        $strip = fn ($html) => preg_replace('/(csrf-token" content="|csrf: ")[^"]+"/', '$1"', preg_replace('/name="_token" value="[^"]+"/', '', $html));

        foreach (['/shop', $this->productPage()] as $url) {
            $this->assertSame($strip($this->withHeaders($desktop)->get($url)->getContent()),
                $strip($this->withHeaders($phone)->get($url)->getContent()), $url);
        }
        $shop = $this->get('/shop')->getContent();
        $this->assertStringContainsString('id="shop-nav"', $shop);         // category buttons
        $this->assertStringContainsString('id="shop-sidebar"', $shop);     // filters (a sheet on phones)
        $this->assertStringContainsString('data-open-filters', $shop);      // phone filters button
        $this->assertFalse(class_exists('Jenssegers\Agent\Agent'));
    }

    // ---------- queries ----------

    public function test_category_menu_comes_from_the_cache(): void
    {
        $this->get('/cart');                       // fills the cache
        $this->assertSame(0, $this->queries('/cart'));
        $this->assertSame(0, $this->queries('/checkout'));
        $this->assertLessThanOrEqual(10, $this->queries('/'));
        $this->assertLessThanOrEqual(7, $this->queries('/shop'));
        $this->assertLessThanOrEqual(8, $this->queries($this->productPage()));
    }

    public function test_category_changes_show_in_the_menu_at_once(): void
    {
        $this->get('/cart');

        $category = new Category();
        $category->name = 'P3 Test Category';
        $category->save();
        $this->assertStringContainsString('P3 Test Category', $this->get('/cart')->getContent());

        $category->name = 'P3 Renamed Category';
        $category->save();
        $html = $this->get('/cart')->getContent();
        $this->assertStringContainsString('P3 Renamed Category', $html);
        $this->assertStringNotContainsString('P3 Test Category', $html);

        $category->delete();
        $this->assertStringNotContainsString('P3 Renamed Category', $this->get('/cart')->getContent());
    }

    public function test_related_products_are_limited_and_from_the_same_category(): void
    {
        $categoryId = DB::table('products')->whereNull('deleted_at')->groupBy('category_id')
            ->orderByRaw('count(*) desc')->value('category_id');
        $product = Product::where('category_id', $categoryId)->first();

        $related = $this->get($this->productUrl($product->id))->assertOk()->viewData('relatedProducts');

        $this->assertLessThanOrEqual(8, $related->count());
        $this->assertGreaterThan(0, $related->count());
        $this->assertTrue($related->every(fn ($p) => $p->category_id == $categoryId && $p->id != $product->id));
        $this->assertNotNull($related->first()->reviews_count);
    }

    public function test_shop_filters_still_work(): void
    {
        $category = Category::whereHas('getProducts')->first();
        // A main category lists its subcategories' products too
        $expected = Product::whereIn('category_id', $category->familyIds())->count();

        foreach (['category', 'category-phone'] as $field) {
            $json = $this->getJson('/shop?' . http_build_query([$field => $category->name]), ['X-Requested-With' => 'XMLHttpRequest'])
                ->assertOk()->json();
            $this->assertSame($expected, $json['total'], $field);
            $this->assertArrayHasKey('productList', $json);
            $this->assertArrayHasKey('paginatee', $json);
        }

        $all = Product::count();
        $this->get('/shop?category=show-all')->assertStatus(301)->assertRedirect('/shop');
        $html = $this->get('/shop')->assertOk()->getContent();
        $this->assertStringContainsString("of $all products", $html);
        // The search finds every product whose name or brand has the word, and more (its category, typos)
        $whey = Product::where('name', 'like', '%whey%')->orWhereIn('brand_id', \App\Models\Brand::where('name', 'like', '%whey%')->select('id'))->pluck('id')->all();
        $found = \App\Services\ProductSearch::ids('whey');
        $this->assertSame([], array_diff($whey, $found));
        $this->assertStringContainsString('of ' . count($found) . ' products', $this->get('/shop?shop-search=whey')->getContent());
    }

    // ---------- emails after the response ----------

    public function test_newsletter_answers_at_once_and_sends_after_the_response(): void
    {
        Bus::fake();
        $product = Product::first();
        $active = Subscription::where('is_active', true)->count();

        $this->actingAs(User::first())->postJson('/admin/products/send-email', ['product_id' => $product->id])
            ->assertOk()->assertJson(['success' => true])
            ->assertJsonFragment(['message' => "Sending \"{$product->name}\" to {$active} subscriber(s) now. You can keep working; failures are written to the log."]);

        Bus::assertDispatchedAfterResponse(SendProductNewsletter::class, fn ($job) => $job->productId === $product->id);
    }

    public function test_newsletter_job_emails_every_active_subscriber(): void
    {
        // The array mailer really builds and "sends" each email, in memory
        config(['mail.default' => 'array']);
        $active = Subscription::where('is_active', true)->pluck('email');
        $this->assertNotEmpty($active);

        (new SendProductNewsletter(Product::first()->id))->handle();

        $sent = collect(app('mail.manager')->mailer('array')->getSymfonyTransport()->messages())
            ->map(fn ($m) => $m->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertEquals($active->sort()->values(), $sent->sort()->values());
    }

    public function test_contact_form_email_goes_out_after_the_response(): void
    {
        Mail::fake();
        $this->withoutDefer();

        $this->post('/contact/send', ['name' => 'P3', 'email' => 'p3@example.test', 'phone' => '70000000', 'message' => 'hello'])
            ->assertRedirect();

        Mail::assertSent(ContactMail::class, fn ($mail) => $mail->hasTo('admin@supplement-station.com'));
    }

    // ---------- production ----------

    public function test_routes_can_be_cached_and_admin_order_link_is_unchanged(): void
    {
        $this->assertSame(url('/admin/orders/5'), route('admin.orders.details', 5));
        $names = collect(app('router')->getRoutes()->getRoutes())->map->getName()->filter();
        $this->assertSame($names->count(), $names->unique()->count(), 'route names must be unique for route:cache');
    }

    public function test_build_files_get_a_long_cache_header(): void
    {
        $this->assertStringContainsString('max-age=31536000', file_get_contents(public_path('assets/build/.htaccess')));
        $this->assertStringContainsString('max-age=31536000', file_get_contents(public_path('assets/fonts/.htaccess')));
    }
}
