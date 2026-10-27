<?php

namespace Tests\Feature\Store;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Review;
use App\Support\Slugs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Name-based addresses, redirects from the old ones, and what search engines read
class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::factory()->withImage()->create($attributes);
    }

    // The JSON-LD items of a page, by @type
    private function schema(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $this->assertCount(1, $m[1], 'one JSON-LD block');
        $data = json_decode($m[1][0], true);
        $this->assertIsArray($data, 'JSON-LD is valid JSON');
        $this->assertSame('https://schema.org', $data['@context']);

        return collect($data['@graph'])->keyBy('@type')->all();
    }

    // ---------- slugs ----------

    public function test_slugs_are_made_from_the_name(): void
    {
        $this->assertSame('optimum-nutrition-whey-gold-standard-2-27kg', Slugs::fromName('Optimum Nutrition Whey Gold Standard (2.27kg)', 'product'));
        $this->assertSame('product-100', Slugs::fromName('100', 'product'));
        $this->assertSame('product', Slugs::fromName('!!!', 'product'));
    }

    public function test_same_names_get_a_number_and_archived_products_keep_theirs(): void
    {
        $category = Category::factory()->create();
        $make = fn () => Product::factory()->create(['name' => 'Test Shaker', 'category_id' => $category->id]);

        $a = $make();
        $b = $make();
        $this->assertSame('test-shaker', $a->slug);
        $this->assertSame('test-shaker-2', $b->slug);

        $a->delete();
        $this->assertSame('test-shaker-3', $make()->slug);
    }

    public function test_saving_without_renaming_keeps_the_slug(): void
    {
        $product = $this->product();
        $slug = $product->slug;
        $product->update(['price' => $product->price + 1]);

        $this->assertSame($slug, $product->fresh()->slug);
        $this->assertSame(0, DB::table('old_slugs')->count());
    }

    public function test_a_renamed_product_moves_and_its_old_address_redirects(): void
    {
        $product = $this->product(['name' => 'Old Name']);
        $product->update(['name' => 'Renamed Product']);

        $this->assertSame('renamed-product', $product->slug);
        $this->get('/products/old-name')->assertStatus(301)->assertRedirect('/products/renamed-product');
        $this->get('/products/renamed-product')->assertOk();

        // Renamed again and back: the address in use is never also listed as an old one
        $product->update(['name' => 'Something Else']);
        $product->update(['name' => 'Renamed Product']);
        $this->assertSame('renamed-product', $product->fresh()->slug);
        $this->assertNull(Product::idForOldSlug('renamed-product'));
        $this->assertSame($product->id, Product::idForOldSlug('something-else'));
    }

    public function test_a_renamed_category_moves_the_menu_and_the_old_address_redirects(): void
    {
        $category = Category::factory()->create(['name' => 'Old Category']);
        $this->product(['category_id' => $category->id]);
        $this->get('/')->assertOk();        // fills the menu cache

        $category->update(['name' => 'Renamed Category']);

        $this->assertStringContainsString('/shop/renamed-category"', $this->get('/')->getContent());
        $this->get('/shop/old-category')->assertStatus(301)->assertRedirect('/shop/renamed-category');
    }

    // ---------- addresses ----------

    public function test_old_product_addresses_redirect_permanently(): void
    {
        $product = $this->product();

        $this->get("/products/{$product->id}")->assertStatus(301)->assertRedirect($product->url());
        $this->get('/products/' . strtoupper($product->slug))->assertStatus(301)->assertRedirect($product->url());
        $this->get("/products/{$product->id}?utm_source=ig")->assertStatus(301)->assertRedirect($product->url() . '?utm_source=ig');
        $this->get($product->url())->assertOk();
        $this->get('/products/no-such-product')->assertNotFound();
        $this->get('/products/99999999')->assertNotFound();
    }

    public function test_an_archived_product_sends_visitors_to_its_category(): void
    {
        $product = $this->product();
        $category = $product->getCategory;
        $product->delete();

        $this->get("/products/{$product->slug}")->assertStatus(302)->assertRedirect($category->url());
        $this->get("/products/{$product->id}")->assertStatus(302)->assertRedirect($category->url());
    }

    public function test_lowercase_pages_and_old_mixed_case_redirects(): void
    {
        foreach (['/', '/cart', '/checkout'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/Shop')->assertStatus(301)->assertRedirect(url('/') . '/');
        $this->get('/viewCart')->assertStatus(301)->assertRedirect('/cart');
        $this->get('/Checkout')->assertStatus(301)->assertRedirect('/checkout');
        $this->get('/Shop/search?search=whey')->assertStatus(301)->assertRedirect(url('/') . '/?shop-search=whey');
        $this->get('/Shop?shop-search=whey')->assertStatus(301)->assertRedirect(url('/') . '/?shop-search=whey');
    }

    public function test_old_category_filters_redirect_to_the_category_page(): void
    {
        $category = Category::factory()->create(['name' => 'Creatine']);
        $this->product(['category_id' => $category->id]);

        $this->get("/Shop?category-sidebar={$category->id}&orderby=low-to-high")->assertStatus(301)->assertRedirect($category->url() . '?orderby=low-to-high');
        $this->get("/Shop?category-nav={$category->id}&_token=abc")->assertStatus(301)->assertRedirect($category->url());
        $this->get('/?category=Creatine')->assertStatus(301)->assertRedirect($category->url());
        $this->get('/?category=show-all')->assertStatus(301)->assertRedirect(url('/') . '/');
        $this->get("/shop/{$category->id}")->assertStatus(301)->assertRedirect($category->url());
        $this->get('/shop/Creatine')->assertStatus(301)->assertRedirect('/shop/creatine');
        $this->get('/shop/no-such-category')->assertNotFound();
    }

    public function test_a_category_page_lists_only_that_category_and_keeps_sorting_and_paging(): void
    {
        $category = Category::factory()->create(['name' => 'Protein']);
        Product::factory()->count(25)->withImage()->create(['category_id' => $category->id]);
        $other = $this->product();

        $html = $this->get($category->url())->assertOk()->getContent();
        $this->assertStringContainsString('>Protein</h1>', $html);
        $this->assertStringContainsString('<title>Protein in Lebanon | Supplement Station</title>', $html);

        $json = $this->getJson($category->url() . '?orderby=high-to-low', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json();
        $this->assertStringNotContainsString(e($other->name), $json['productList']);
        $this->assertStringNotContainsString(e($other->name), $json['cards']);
        $this->assertSame($category->url() . '?orderby=high-to-low&page=2', $json['next'], 'load more keeps the sorting');

        $this->get($category->url() . '?page=2')->assertOk();
        $this->get($category->url() . '?page=999')->assertNotFound();
    }

    // ---------- what search engines read ----------

    public function test_product_page_title_description_canonical_and_product_data(): void
    {
        $product = $this->product(['price' => 50, 'discount' => 10]);
        $html = $this->get($product->url())->assertOk()->getContent();

        $this->assertStringContainsString('<title>' . e($product->name) . ' Price in Lebanon | Supplement Station</title>', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . $product->url() . '">', $html);
        $this->assertMatchesRegularExpression('#<meta name="description" content="Buy [^"]{20,}">#', $html);
        $this->assertStringContainsString('<meta property="og:type" content="product">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="' . asset($product->images->first()->url) . '">', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<h1>' . e($product->name) . '</h1>', $html);

        $schema = $this->schema($html);
        $this->assertSame($product->name, $schema['Product']['name']);
        $this->assertSame('45.00', $schema['Product']['offers']['price']);
        $this->assertSame('USD', $schema['Product']['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $schema['Product']['offers']['availability']);
        $this->assertSame(asset($product->images->first()->url), $schema['Product']['image'][0]);
        $this->assertSame($product->url(), end($schema['BreadcrumbList']['itemListElement'])['item']);
        $this->assertArrayNotHasKey('aggregateRating', $schema['Product']);
    }

    public function test_reviews_and_sold_out_show_in_product_data(): void
    {
        $product = $this->product(['stock' => false, 'price' => 20, 'discount' => 10]);
        Review::factory()->create(['product_id' => $product->id, 'name' => 'A </script><b>', 'rating' => 5]);
        Review::factory()->create(['product_id' => $product->id, 'name' => 'B', 'rating' => 4]);

        $html = $this->get($product->url())->assertOk()->getContent();
        $schema = $this->schema($html);

        $this->assertSame('https://schema.org/OutOfStock', $schema['Product']['offers']['availability']);
        $this->assertSame('18.00', $schema['Product']['offers']['price']);
        $this->assertEquals(4.5, $schema['Product']['aggregateRating']['ratingValue']);
        $this->assertSame(2, $schema['Product']['aggregateRating']['reviewCount']);
        $this->assertStringNotContainsString('</script><b>', $html);  // a review name cannot close the tag
    }

    public function test_front_page_describes_the_store(): void
    {
        Faq::factory()->count(2)->create();
        $this->product();

        // The shop is the front page: it carries the store's name, details and site search
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<title>Supplement Station Lebanon | Protein, Creatine &amp; Gym Supplements</title>', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/') . '/">', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow', $html);
        $schema = $this->schema($html);
        $this->assertSame('LB', $schema['Store']['address']['addressCountry']);
        $this->assertSame('+961 81 088 266', $schema['Store']['telephone']);
        $this->assertArrayHasKey('WebSite', $schema);

        // Page 2 and category pages keep their own titles
        $this->assertStringNotContainsString('Protein, Creatine &amp; Gym Supplements</title>', $this->get('/shop/' . $this->product()->getCategory->slug)->getContent());

        // /home has its own title and is indexed under its own address
        $home = $this->get('/home')->assertOk()->getContent();
        $this->assertStringContainsString('<title>Best Sellers &amp; Special Offers | Supplement Station Lebanon</title>', $home);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/home') . '">', $home);
        $this->assertCount(2, $this->schema($home)['FAQPage']['mainEntity']);

        // The old /shop address still opens the shop, with its filters
        $this->get('/shop')->assertRedirect(url('/') . '/')->assertStatus(301);
        $this->get('/shop?orderby=newest')->assertStatus(301)->assertRedirectContains('orderby=newest');
    }

    public function test_every_store_page_has_one_title_description_and_canonical(): void
    {
        $product = $this->product();
        $category = $product->getCategory;

        foreach (['/home', '/', $category->url(), $product->url(), '/cart', '/checkout'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, '<title>'), $url);
            $this->assertSame(1, substr_count($html, '<meta name="description"'), $url);
            $this->assertSame(1, substr_count($html, '<link rel="canonical"'), $url);
            $this->assertStringNotContainsString('<?php', $html, $url);
            $this->assertStringNotContainsString('_token=', $html, $url);
            $this->assertDoesNotMatchRegularExpression('#(src|data-background)="assets/#', $html, $url);
            $this->assertStringNotContainsString('index.html', $html, $url);
            $this->assertStringNotContainsString('/products/' . $product->id . '"', $html, $url);
        }
    }

    public function test_cart_checkout_and_search_results_are_not_indexed(): void
    {
        foreach (['/cart', '/checkout', '/?shop-search=whey'] as $url) {
            $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $this->get($url)->getContent(), $url);
        }
        $this->product();
        $this->assertStringContainsString('<meta name="robots" content="index, follow', $this->get('/')->getContent());
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/') . '/">', $this->get('/?orderby=low-to-high')->getContent());
    }

    public function test_menu_links_go_to_category_pages(): void
    {
        $categories = Category::factory()->count(3)->create();
        // The menus only list categories that have products
        $categories->each(fn ($category) => Product::factory()->create(['category_id' => $category->id]));

        $html = $this->get('/home')->getContent();
        foreach ($categories as $category) {
            $this->assertStringContainsString('<a href="' . $category->url() . '">', $html, $category->name);
        }
        $this->assertStringNotContainsString('category-nav', $html);
    }

    // ---------- sitemap and robots.txt ----------

    public function test_sitemap_lists_every_shop_product_and_category_page(): void
    {
        $products = Product::factory()->count(3)->withImage()->create();
        $archived = $this->product();
        $archived->delete();

        $response = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));
        $xml = simplexml_load_string($response->getContent());
        $locs = collect();
        foreach ($xml->url as $url) {
            $locs->push((string) $url->loc);
        }

        foreach ($products as $product) {
            $this->assertContains($product->url(), $locs);
            $this->assertContains($product->getCategory->url(), $locs);
        }
        $this->assertNotContains($archived->url(), $locs);
        $this->assertNotContains($archived->getCategory->url(), $locs, 'a category with no products left is not listed');
        $this->assertContains(url('/') . '/', $locs);
        $this->assertContains(route('home'), $locs);
        $this->assertSame($locs->count(), $locs->unique()->count());
        $this->assertFileDoesNotExist(public_path('sitemap.xml'));   // a file there would hide this page
    }

    public function test_robots_txt_points_to_the_sitemap_and_blocks_only_form_addresses(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Sitemap: https://supplement-station.com/sitemap.xml', $robots);
        $this->assertStringNotContainsString("Disallow: /\n", $robots);

        preg_match_all('/^Disallow: (\S+)/m', $robots, $m);
        $blocked = fn (string $path) => collect($m[1])->contains(fn ($rule) => str_starts_with($path, $rule));
        $product = $this->product();
        foreach (['/home', '/', parse_url($product->getCategory->url(), PHP_URL_PATH), parse_url($product->url(), PHP_URL_PATH), '/cart', '/checkout', '/sitemap.xml'] as $path) {
            $this->assertFalse($blocked($path), "$path must stay open to search engines");
        }
        foreach (["/product/{$product->id}/review", '/cart/add', '/cart/update', '/cart/remove', '/coupon/apply', '/contact/send', '/subscribe', '/subscription/cancel', '/place-order', '/api/cart'] as $path) {
            $this->assertTrue($blocked($path), "$path should be blocked");
        }
        $this->get("/product/{$product->id}/review")->assertStatus(405);
    }

    public function test_emails_use_the_store_logo_and_product_addresses(): void
    {
        $this->assertFileExists(public_path('assets/img/logo/preloader2.png'));
        $checkout = file_get_contents(resource_path('views/mails/checkout_mail.blade.php'));
        $this->assertStringContainsString("asset('assets/img/logo/preloader2.png')", $checkout);
        $this->assertStringNotContainsString('gstatic.com', $checkout);
        $newsletter = file_get_contents(resource_path('views/mails/product-newsletter.blade.php'));
        $this->assertStringContainsString('$product->url()', $newsletter);
        $this->assertStringContainsString("asset('assets/img/logo/preloader2.png')", $newsletter);
    }
}
