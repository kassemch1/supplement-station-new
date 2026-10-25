<?php

use App\Models\Category;
use App\Models\Product;
use App\Support\Slugs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase4SeoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('store.categories');
    }

    private function liveProduct(): Product
    {
        return Product::has('images')->where('stock', '>', 0)->orderBy('id')->firstOrFail();
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

    public function test_every_product_and_category_has_a_unique_slug_from_its_name(): void
    {
        foreach (['products', 'categories'] as $table) {
            $rows = DB::table($table)->get(['id', 'name', 'slug']);
            $this->assertSame(0, $rows->whereNull('slug')->count(), $table);
            $this->assertSame($rows->count(), $rows->pluck('slug')->unique()->count(), $table);
            foreach ($rows as $row) {
                $this->assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $row->slug, "$table {$row->id}");
                $this->assertFalse(ctype_digit($row->slug), "$table {$row->id}");
            }
        }
        $this->assertSame('optimum-nutrition-whey-gold-standard-2-27kg', Slugs::fromName('Optimum Nutrition Whey Gold Standard (2.27kg)', 'product'));
        $this->assertSame('product-100', Slugs::fromName('100', 'product'));
        $this->assertSame('product', Slugs::fromName('!!!', 'product'));
    }

    public function test_new_products_get_a_slug_and_same_names_get_a_number(): void
    {
        $category = Category::first();
        $a = Product::create(['name' => 'P4 Test Shaker', 'description' => 'x', 'price' => 5, 'category_id' => $category->id, 'stock' => 1, 'discount' => 0]);
        $b = Product::create(['name' => 'P4 Test Shaker', 'description' => 'x', 'price' => 5, 'category_id' => $category->id, 'stock' => 1, 'discount' => 0]);

        $this->assertSame('p4-test-shaker', $a->slug);
        $this->assertSame('p4-test-shaker-2', $b->slug);

        // Archived products keep their slug, so a new product cannot take it
        $a->delete();
        $c = Product::create(['name' => 'P4 Test Shaker', 'description' => 'x', 'price' => 5, 'category_id' => $category->id, 'stock' => 1, 'discount' => 0]);
        $this->assertSame('p4-test-shaker-3', $c->slug);
    }

    public function test_saving_without_renaming_keeps_the_slug(): void
    {
        $product = $this->liveProduct();
        $slug = $product->slug;
        $product->update(['price' => $product->price + 1, 'stock' => $product->stock]);

        $this->assertSame($slug, $product->fresh()->slug);
        $this->assertSame(0, DB::table('old_slugs')->count());
    }

    public function test_renamed_product_moves_and_its_old_address_redirects(): void
    {
        $product = $this->liveProduct();
        $old = $product->slug;
        $product->name = 'P4 Renamed Product';
        $product->save();

        $this->assertSame('p4-renamed-product', $product->slug);
        $this->get("/products/$old")->assertStatus(301)->assertRedirect('/products/p4-renamed-product');
        $this->get('/products/p4-renamed-product')->assertOk();

        // Renamed again and back: the address in use is never also listed as an old one
        $product->update(['name' => 'Something Else P4']);
        $product->update(['name' => 'P4 Renamed Product']);
        $this->assertSame('p4-renamed-product', $product->fresh()->slug);
        $this->assertNull(Product::idForOldSlug('p4-renamed-product'));
        $this->assertSame($product->id, Product::idForOldSlug('something-else-p4'));
    }

    public function test_renamed_category_moves_the_menu_and_the_old_address_redirects(): void
    {
        $category = Category::whereHas('getProducts')->firstOrFail();
        $old = $category->slug;
        $this->get('/shop')->assertOk();        // fills the menu cache

        $category->name = 'P4 Renamed Category';
        $category->save();

        $this->assertStringContainsString('/shop/p4-renamed-category"', $this->get('/shop')->getContent());
        $this->get("/shop/$old")->assertStatus(301)->assertRedirect('/shop/p4-renamed-category');
    }

    // ---------- addresses ----------

    public function test_old_product_addresses_redirect_permanently(): void
    {
        $product = $this->liveProduct();

        $this->get("/products/{$product->id}")->assertStatus(301)->assertRedirect($product->url());
        $this->get('/products/' . strtoupper($product->slug))->assertStatus(301)->assertRedirect($product->url());
        $this->get("/products/{$product->id}?utm_source=ig")->assertStatus(301)->assertRedirect($product->url() . '?utm_source=ig');
        $this->get($product->url())->assertOk();
        $this->get('/products/no-such-product-p4')->assertNotFound();
        $this->get('/products/99999999')->assertNotFound();
    }

    public function test_archived_product_sends_visitors_to_its_category(): void
    {
        $product = $this->liveProduct();
        $category = Category::find($product->category_id);
        $product->delete();

        $this->get("/products/{$product->slug}")->assertStatus(302)->assertRedirect($category->url());
        $this->get("/products/{$product->id}")->assertStatus(302)->assertRedirect($category->url());
    }

    public function test_lowercase_pages_and_old_mixed_case_redirects(): void
    {
        foreach (['/shop', '/cart', '/checkout'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/Shop')->assertStatus(301)->assertRedirect('/shop');
        $this->get('/viewCart')->assertStatus(301)->assertRedirect('/cart');
        $this->get('/Checkout')->assertStatus(301)->assertRedirect('/checkout');
        $this->get('/Shop/search?search=whey')->assertStatus(301)->assertRedirect('/shop?shop-search=whey');
        $this->get('/Shop?shop-search=whey')->assertStatus(301)->assertRedirect('/shop?shop-search=whey');

        // The mini-cart script used to read /cart as JSON
        $this->getJson('/cart')->assertOk()->assertJsonStructure(['items']);
    }

    public function test_old_category_filters_redirect_to_the_category_page(): void
    {
        $category = Category::whereHas('getProducts')->firstOrFail();

        $this->get("/Shop?category-sidebar={$category->id}&orderby=low-to-high")->assertStatus(301)->assertRedirect($category->url() . '?orderby=low-to-high');
        $this->get("/Shop?category-nav={$category->id}&_token=abc")->assertStatus(301)->assertRedirect($category->url());
        $this->get('/shop?category=' . urlencode($category->name))->assertStatus(301)->assertRedirect($category->url());
        $this->get('/shop?category=show-all')->assertStatus(301)->assertRedirect('/shop');
        $this->get("/shop/{$category->id}")->assertStatus(301)->assertRedirect($category->url());
        $this->get('/shop/' . rawurlencode($category->name))->assertStatus(301)->assertRedirect($category->url());
        $this->get('/shop/no-such-category-p4')->assertNotFound();
    }

    public function test_category_page_lists_only_that_category_and_keeps_sorting_and_paging(): void
    {
        // The category with the most products, its subcategories included
        $category = Category::findOrFail(\App\Services\Catalog::categoryCounts()->sortDesc()->keys()->first());
        $html = $this->get($category->url())->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<h1 class="ss-shop-title__h">' . e($category->name) . '</h1>', $html);
        $this->assertStringContainsString('<title>' . e($category->name) . ' in Lebanon | Supplement Station</title>', $html);

        $other = Product::whereNotIn('category_id', $category->familyIds())->value('name');
        $json = $this->getJson($category->url() . '?orderby=high-to-low', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json();
        $this->assertStringNotContainsString(e($other), $json['productList']);
        // 24 a page; the next page keeps the sorting
        $this->assertGreaterThan(24, $json['total']);
        $this->assertStringContainsString($category->url() . '?orderby=high-to-low&page=2', (string) $json['next']);

        $this->get($category->url() . '?page=999')->assertNotFound();
    }

    // ---------- what search engines read ----------

    public function test_product_page_has_title_description_canonical_and_product_data(): void
    {
        $product = $this->liveProduct();
        $html = $this->get($product->url())->assertOk()->getContent();

        $this->assertStringContainsString('<title>' . e($product->name) . ' Price in Lebanon | Supplement Station</title>', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . $product->url() . '">', $html);
        $this->assertMatchesRegularExpression('#<meta name="description" content="Buy [^"]{20,}">#', $html);
        $this->assertStringContainsString('<meta property="og:type" content="product">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="' . asset($product->images->first()->url) . '">', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<h1>' . e($product->name) . '</h1>', $html);
        $this->assertStringNotContainsString('Travelling salesman', $html);

        $schema = $this->schema($html);
        $this->assertSame($product->name, $schema['Product']['name']);
        $this->assertSame(number_format($product->price_after_discount, 2, '.', ''), $schema['Product']['offers']['price']);
        $this->assertSame('USD', $schema['Product']['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $schema['Product']['offers']['availability']);
        $this->assertSame(asset($product->images->first()->url), $schema['Product']['image'][0]);
        $this->assertSame($product->url(), end($schema['BreadcrumbList']['itemListElement'])['item']);
    }

    public function test_reviews_and_sold_out_show_in_product_data(): void
    {
        $product = $this->liveProduct();
        $product->update(['stock' => 0, 'discount' => 10]);
        DB::table('reviews')->where('product_id', $product->id)->delete();
        DB::table('reviews')->insert([
            ['product_id' => $product->id, 'name' => 'A </script><b>', 'message' => 'Great', 'rating' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $product->id, 'name' => 'B', 'message' => 'Ok', 'rating' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $html = $this->get($product->url())->assertOk()->getContent();
        $schema = $this->schema($html);

        $this->assertSame('https://schema.org/OutOfStock', $schema['Product']['offers']['availability']);
        $this->assertSame(number_format($product->price * 0.9, 2, '.', ''), $schema['Product']['offers']['price']);
        $this->assertEquals(4.5, $schema['Product']['aggregateRating']['ratingValue']);
        $this->assertSame(2, $schema['Product']['aggregateRating']['reviewCount']);
        $this->assertStringNotContainsString('</script><b>', $html);  // a review name cannot close the tag
    }

    public function test_product_without_reviews_has_no_rating_data(): void
    {
        $product = $this->liveProduct();
        DB::table('reviews')->where('product_id', $product->id)->delete();

        $schema = $this->schema($this->get($product->url())->getContent());
        $this->assertArrayNotHasKey('aggregateRating', $schema['Product']);
        $this->assertArrayNotHasKey('review', $schema['Product']);
    }

    public function test_home_page_describes_the_store(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Supplement Station Lebanon | Protein, Creatine &amp; Gym Supplements</title>', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/') . '/">', $html);
        $schema = $this->schema($html);
        $this->assertSame('LB', $schema['Store']['address']['addressCountry']);
        $this->assertSame('+961 81 088 266', $schema['Store']['telephone']);
        $this->assertArrayHasKey('WebSite', $schema);
        if (DB::table('faqs')->exists()) {
            $this->assertCount(DB::table('faqs')->count(), $schema['FAQPage']['mainEntity']);
        }
    }

    public function test_every_store_page_has_one_title_description_and_canonical_and_no_blade_leftovers(): void
    {
        $product = $this->liveProduct();
        $category = Category::whereHas('getProducts')->firstOrFail();

        foreach (['/', '/shop', $category->url(), $product->url(), '/cart', '/checkout'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, '<title>'), $url);
            $this->assertSame(1, substr_count($html, '<meta name="description"'), $url);
            $this->assertSame(1, substr_count($html, '<link rel="canonical"'), $url);
            $this->assertStringNotContainsString('<?php', $html, $url);
            $this->assertStringNotContainsString('_token=', $html, $url);
            $this->assertDoesNotMatchRegularExpression('#(src|data-background)="assets/#', $html, $url);
            $this->assertStringNotContainsString('tel:02456787535', $html, $url);
            $this->assertStringNotContainsString('index.html', $html, $url);
            $this->assertStringNotContainsString('/products/' . $product->id . '"', $html, $url);
        }
    }

    public function test_cart_checkout_and_search_results_are_not_indexed(): void
    {
        foreach (['/cart', '/checkout', '/shop?shop-search=whey'] as $url) {
            $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $this->get($url)->getContent(), $url);
        }
        $this->assertStringContainsString('<meta name="robots" content="index, follow', $this->get('/shop')->getContent());
        $this->assertStringContainsString('<link rel="canonical" href="' . route('shop') . '">', $this->get('/shop?orderby=low-to-high')->getContent());
    }

    public function test_menu_links_go_to_category_pages(): void
    {
        $html = $this->get('/')->getContent();
        foreach (Category::all() as $category) {
            $this->assertMatchesRegularExpression('#<a href="' . preg_quote($category->url(), '#') . '"[ >]#', $html, $category->name);
        }
        $this->assertStringNotContainsString('category-nav', $html);
    }

    // ---------- sitemap, robots.txt, emails ----------

    public function test_sitemap_lists_every_shop_product_and_category_page(): void
    {
        $archived = $this->liveProduct();
        $archived->delete();

        $response = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $locs = collect();
        foreach ($xml->url as $url) {
            $locs->push((string) $url->loc);
        }

        foreach (Product::all() as $product) {
            $this->assertContains($product->url(), $locs, $product->name);
        }
        $this->assertNotContains($archived->url(), $locs);
        $this->assertContains(route('shop'), $locs);
        foreach (Category::whereHas('getProducts')->get() as $category) {
            $this->assertContains($category->url(), $locs, $category->name);
        }
        $this->assertSame($locs->count(), $locs->unique()->count());
        $this->assertFileDoesNotExist(public_path('sitemap.xml'));   // a file there would hide this page
    }

    public function test_robots_txt_points_to_the_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Sitemap: https://supplement-station.com/sitemap.xml', $robots);
        $this->assertStringNotContainsString("Disallow: /\n", $robots);
    }

    // Google reported form-only addresses (e.g. the review form's /product/27/review) as "4xx" pages
    public function test_robots_txt_blocks_form_addresses_and_never_a_page(): void
    {
        preg_match_all('/^Disallow: (\S+)/m', file_get_contents(public_path('robots.txt')), $m);
        $blocked = fn (string $path) => collect($m[1])->contains(fn ($rule) => str_starts_with($path, $rule));

        $product = $this->liveProduct();
        $category = Category::whereHas('getProducts')->firstOrFail();
        foreach (['/', '/shop', $category->url(), $product->url(), '/cart', '/checkout', '/sitemap.xml'] as $url) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $this->assertFalse($blocked($path), "$path must stay open to search engines");
        }

        foreach (["/product/{$product->id}/review", '/cart/add', '/cart/update', '/cart/remove', '/coupon/apply', '/contact/send', '/subscribe', '/subscription/cancel', '/place-order', '/api/cart'] as $path) {
            $this->assertTrue($blocked($path), "$path should be blocked");
        }
        $this->get("/product/{$product->id}/review")->assertStatus(405);   // what Google kept getting
    }

    public function test_emails_use_the_store_logo_and_product_addresses(): void
    {
        $this->assertFileExists(public_path('assets/img/logo/preloader2.png'));

        $source = file_get_contents(resource_path('views/mails/checkout_mail.blade.php'));
        $this->assertStringContainsString("asset('assets/img/logo/preloader2.png')", $source);
        $this->assertStringNotContainsString('gstatic.com', $source);

        $source = file_get_contents(resource_path('views/mails/product-newsletter.blade.php'));
        $this->assertStringContainsString('$product->url()', $source);
        $this->assertStringContainsString("asset('assets/img/logo/preloader2.png')", $source);
    }
}
