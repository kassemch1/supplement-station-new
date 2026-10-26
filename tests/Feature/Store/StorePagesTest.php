<?php

namespace Tests\Feature\Store;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SpecialOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Home, shop and product pages: the shared layout, the cards, and how many queries they take
class StorePagesTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = ['/', '/shop', '/cart', '/checkout'];

    private function queries(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    private function linksTo(string $html, Product $product): int
    {
        return substr_count($html, 'href="' . $product->url() . '"');
    }

    // ---------- layout and bundles ----------

    public function test_built_css_and_js_match_their_sources(): void
    {
        $this->assertSame(0, Artisan::call('assets:build', ['--check' => true]), Artisan::output());
    }

    public function test_every_store_page_uses_the_layout_and_the_bundles(): void
    {
        $product = Product::factory()->withImage()->create();

        foreach ([...self::PAGES, $product->url()] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('#assets/build/store\.css\?v=[0-9a-f]{10}#', $html, $url);
            $this->assertMatchesRegularExpression('#<script src="[^"]*assets/build/store\.js\?v=[0-9a-f]{10}" defer>#', $html, $url);
            $this->assertStringContainsString('window.SS = {', $html, $url);
            foreach (['jquery-3.7.1.min.js', 'jquery-3.6.0', 'jquery-ui', 'magnific', 'inview', 'scrollspy', '<style>'] as $gone) {
                $this->assertStringNotContainsString($gone, $html, "$url still has $gone");
            }
            $this->assertSame(1, substr_count($html, '<header'), "$url renders the header once");
            $this->assertSame(1, substr_count($html, '<footer'), "$url renders the footer once");
        }

        $this->assertStringContainsString('assets/build/shop.js', $this->get('/shop')->getContent());
        $this->assertStringContainsString('assets/build/cart.js', $this->get('/cart')->getContent());
        $this->assertStringContainsString('assets/build/product.js', $this->get($product->url())->getContent());
        $home = $this->get('/')->getContent();
        $this->assertStringContainsString('assets/build/home.css', $home);
        $this->assertStringNotContainsString('assets/build/shop.js', $home);
    }

    public function test_phone_and_desktop_get_the_same_page(): void
    {
        $product = Product::factory()->withImage()->create();
        $phone = ['User-Agent' => 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 Mobile Safari/537.36'];
        $desktop = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36'];
        $strip = fn ($html) => preg_replace('/(csrf-token" content="|csrf: ")[^"]+"/', '$1"', preg_replace('/name="_token" value="[^"]+"/', '', $html));

        foreach (['/shop', $product->url()] as $url) {
            $this->assertSame($strip($this->withHeaders($desktop)->get($url)->getContent()), $strip($this->withHeaders($phone)->get($url)->getContent()), $url);
        }
    }

    public function test_build_files_get_a_long_cache_header(): void
    {
        $this->assertStringContainsString('max-age=31536000', file_get_contents(public_path('assets/build/.htaccess')));
        $this->assertStringContainsString('max-age=31536000', file_get_contents(public_path('assets/fonts/.htaccess')));
    }

    // ---------- queries ----------

    public function test_pages_take_a_fixed_small_number_of_queries(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(14)->withImage()->withOptions('Flavor', ['Vanilla' => true])->create(['category_id' => $category->id]);
        $product = Product::first();
        Banner::factory()->create(['product_id' => $product->id]);
        foreach (Product::take(4)->get() as $i => $offer) {
            SpecialOffer::create(['product_id' => $offer->id, 'position' => $i + 1]);
        }
        OrderItem::factory()->count(3)->create(['product_id' => $product->id, 'order_id' => Order::factory()->status('delivered')]);

        $this->get('/cart');   // fills the category menu cache
        $this->assertSame(0, $this->queries('/cart'));
        $this->assertSame(0, $this->queries('/checkout'));
        // 11 on the home page: fewer than 6 products sold, so one more query fills the row
        $this->assertLessThanOrEqual(11, $this->queries('/'));
        $this->assertLessThanOrEqual(7, $this->queries('/shop'));
        $this->assertLessThanOrEqual(8, $this->queries($product->url()));
    }

    public function test_category_changes_show_in_the_menu_at_once(): void
    {
        $this->get('/cart');
        $category = Category::create(['name' => 'Test Category']);
        // The menus only list categories that have products
        $this->assertStringNotContainsString('Test Category', $this->get('/cart')->getContent());
        $product = Product::factory()->create(['category_id' => $category->id]);
        $this->assertStringContainsString('Test Category', $this->get('/cart')->getContent());

        $category->update(['name' => 'Renamed Category']);
        $html = $this->get('/cart')->getContent();
        $this->assertStringContainsString('Renamed Category', $html);
        $this->assertStringNotContainsString('Test Category', $html);

        $product->delete();
        $this->assertStringNotContainsString('Renamed Category', $this->get('/cart')->getContent());
        $category->delete();
        $this->assertStringNotContainsString('Renamed Category', $this->get('/cart')->getContent());
    }

    // ---------- home ----------

    public function test_home_shows_best_sellers_in_sales_order_and_the_admins_special_offers(): void
    {
        [$first, $second, $third] = Product::factory()->count(3)->withImage()->create();
        $delivered = Order::factory()->status('delivered')->create();
        OrderItem::factory()->create(['order_id' => $delivered->id, 'product_id' => $second->id, 'quantity' => 9]);
        OrderItem::factory()->create(['order_id' => $delivered->id, 'product_id' => $third->id, 'quantity' => 4]);
        // pending orders don't count as sales
        OrderItem::factory()->create(['order_id' => Order::factory()->create()->id, 'product_id' => $first->id, 'quantity' => 50]);
        SpecialOffer::create(['product_id' => $third->id, 'position' => 1]);
        SpecialOffer::create(['product_id' => $first->id, 'position' => 2]);

        $response = $this->get('/')->assertOk();

        $this->assertSame([$second->id, $third->id], $response->viewData('product')->take(2)->pluck('id')->all());
        $this->assertCount(3, $response->viewData('product'), 'filled up with other products');
        $this->assertSame([$third->id, $first->id], $response->viewData('offersProducts')->pluck('id')->all());
        $this->assertNotNull($response->viewData('product')->first()->reviews_count);
    }

    public function test_home_works_on_an_empty_shop(): void
    {
        $this->get('/')->assertOk();
        $this->get('/shop')->assertOk()->assertSee('No products found');
    }

    public function test_hero_and_main_product_image_load_first(): void
    {
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<img src="[^"]*"[^>]*fetchpriority="high"(?![^>]*loading="lazy")#', $home);

        $product = Product::factory()->withImage()->create();
        $this->assertMatchesRegularExpression('#width="500" height="500"\s+fetchpriority="high"#', $this->get($product->url())->getContent());
    }

    // ---------- shop ----------

    public function test_shop_search_sort_and_old_filters(): void
    {
        $category = Category::factory()->create(['name' => 'Whey']);
        Product::factory()->withImage()->create(['name' => 'Gold Whey', 'price' => 80, 'category_id' => $category->id]);
        Product::factory()->withImage()->create(['name' => 'Iso Whey', 'price' => 50, 'discount' => 50, 'category_id' => $category->id]);
        Product::factory()->withImage()->create(['name' => 'Creatine', 'price' => 30]);

        $this->assertSame(3, $this->get('/shop')->viewData('totalProducts'));
        $this->assertSame(2, $this->get('/shop?shop-search=whey')->viewData('totalProducts'));
        $this->assertSame(2, $this->get('/shop?home-search=whey')->viewData('totalProducts'));
        $this->assertSame(2, $this->get('/shop?q=whey')->viewData('totalProducts'));

        $sorted = $this->get('/shop?orderby=low-to-high')->viewData('products')->pluck('name')->all();
        $this->assertSame(['Iso Whey', 'Creatine', 'Gold Whey'], $sorted, 'sorted by the price after discount');

        foreach (['category', 'category-phone'] as $field) {
            $json = $this->getJson('/shop?' . http_build_query([$field => 'Whey']), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json();
            $this->assertSame(2, $json['total'], $field);
            $this->assertArrayHasKey('productList', $json);
            $this->assertArrayHasKey('paginatee', $json);
        }
    }

    // ---------- product page ----------

    public function test_sold_out_product_page_is_shown_without_add_to_cart(): void
    {
        $sold = Product::factory()->soldOut()->withImage()->create();
        $html = $this->get($sold->url())->assertOk()->getContent();
        $this->assertStringContainsString('Sold out', $html);
        $this->assertStringNotContainsString('<i class="far fa-shopping-bag"></i> Add to cart', $html);

        $this->get(Product::factory()->withImage()->create()->url())->assertOk()->assertSee('Add to cart');
    }

    public function test_sold_out_cards_link_to_their_page_everywhere(): void
    {
        $category = Category::factory()->create();
        $sold = Product::factory()->soldOut()->withImage()->create(['name' => 'Rare Sold Product', 'category_id' => $category->id]);
        $sibling = Product::factory()->withImage()->create(['category_id' => $category->id]);
        SpecialOffer::create(['product_id' => $sold->id, 'position' => 1]);

        $this->assertGreaterThanOrEqual(2, $this->linksTo($this->get('/shop?shop-search=Rare')->getContent(), $sold), 'shop card: picture and name');
        $this->assertStringNotContainsString('showOutOfStockMessage(event)', $this->get('/shop')->getContent());
        $this->assertGreaterThanOrEqual(2, $this->linksTo($this->get('/')->getContent(), $sold), 'home special offer card');
        $this->assertGreaterThanOrEqual(2, $this->linksTo($this->get($sibling->url())->getContent(), $sold), 'related products card');
    }

    public function test_related_products_come_from_the_same_category_in_stock_first(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->withImage()->create(['category_id' => $category->id]);
        Product::factory()->count(9)->withImage()->create(['category_id' => $category->id]);
        $soldOut = Product::factory()->soldOut()->withImage()->create(['category_id' => $category->id]);
        Product::factory()->withImage()->create();   // another category

        $response = $this->get($product->url())->assertOk();
        $related = $response->viewData('relatedProducts');

        $this->assertCount(8, $related);
        $this->assertTrue($related->every(fn ($p) => $p->category_id === $category->id && $p->id !== $product->id));
        $this->assertFalse($related->contains('id', $soldOut->id), 'in stock first');
        $this->assertNotNull($related->first()->reviews_count);
        $first = $related->first();
        $this->assertMatchesRegularExpression('#href="' . preg_quote($first->url(), '#') . '" class="ss-card__media"#', $response->getContent());
        $this->assertStringContainsString('data-quick="' . $first->id . '"', $response->getContent(), 'quick add on related products in stock');
    }

    public function test_reviews_are_saved_for_real_products_and_rate_limited(): void
    {
        $product = Product::factory()->create();

        $this->postJson("/product/{$product->id}/review", ['name' => 'Ali', 'message' => 'Great', 'rating' => 5])->assertOk()
            ->assertJson(['success' => true, 'review' => ['name' => 'Ali', 'rating' => 5]]);
        $this->assertSame(1, $product->reviews()->count());

        $this->postJson('/product/999999/review', ['name' => 'Ali', 'message' => 'x', 'rating' => 5])->assertNotFound();
        $this->postJson("/product/{$product->id}/review", ['name' => 'Ali', 'message' => 'x', 'rating' => 9])->assertStatus(422);
        $this->postJson('/product/abc/review', ['name' => 'Ali', 'message' => 'x', 'rating' => 5])->assertNotFound();

        for ($i = 0; $i < 2; $i++) {
            $this->postJson("/product/{$product->id}/review", ['name' => 'Ali', 'message' => 'x', 'rating' => 4])->assertOk();
        }
        $this->postJson("/product/{$product->id}/review", ['name' => 'Ali', 'message' => 'x', 'rating' => 4])->assertStatus(429);
    }

    public function test_recent_purchases_show_only_products_in_the_shop(): void
    {
        $shown = Product::factory()->withImage()->create();
        $archived = Product::factory()->withImage()->create();
        foreach ([$shown, $archived] as $product) {
            Order::factory()->placed()->create()->orderItems()->update(['product_id' => $product->id]);
        }
        $archived->delete();

        $this->getJson('/api/recent-purchases')->assertOk()
            ->assertJsonFragment(['product_id' => $shown->id, 'city' => 'Beirut', 'product_url' => $shown->url()])
            ->assertJsonMissing(['product_id' => $archived->id]);
    }
}
