<?php

namespace Tests\Feature\Store;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\SpecialOffer;
use App\Models\User;
use App\Services\PageCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Ready-made store pages (App\Services\PageCache, App\Http\Middleware\CachePages)
class PageCacheTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.page_cache' => true]);
        $category = Category::create(['name' => 'Creatine']);
        $this->product = Product::factory()->withImage()->create(['name' => 'Mono Creatine', 'category_id' => $category->id]);
    }

    // A new visitor: a new session with its own CSRF token
    private function newVisitor(): string
    {
        session()->flush();
        session()->regenerateToken();

        return session()->token();
    }

    private function cacheStatus(string $url, array $headers = []): ?string
    {
        return $this->withHeaders($headers)->get($url)->headers->get('X-Page-Cache');
    }

    public function test_store_pages_are_stored_and_each_visitor_gets_their_own_token(): void
    {
        foreach (['/home', '/', '/?page=1', '/?sort=newest', $this->product->getCategory->url(), $this->product->url(), '/sitemap.xml'] as $url) {
            $first = $this->newVisitor();
            $this->get($url)->assertOk()->assertHeader('X-Page-Cache', 'MISS');

            $second = $this->newVisitor();
            $this->assertNotSame($first, $second);
            $html = $this->get($url)->assertOk()->assertHeader('X-Page-Cache', 'HIT')->getContent();

            $this->assertStringNotContainsString($first, $html, $url);
            $this->assertStringNotContainsString(PageCache::TOKEN, $html, $url);
            if ($url !== '/sitemap.xml') {
                $this->assertStringContainsString('<meta name="csrf-token" content="' . $second . '">', $html, $url);
            }
        }

        // The product page's forms carry the visitor's token too
        $token = $this->newVisitor();
        $html = $this->get($this->product->url())->assertHeader('X-Page-Cache', 'HIT')->getContent();
        $this->assertStringContainsString('name="_token" value="' . $token . '"', $html);
        $this->get('/sitemap.xml')->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function test_a_stored_page_is_the_same_page(): void
    {
        $token = $this->newVisitor();
        $built = $this->get($this->product->url())->getContent();
        $this->newVisitor();
        session()->put('_token', $token);   // same token, so the two pages must match exactly
        $stored = $this->get($this->product->url())->assertHeader('X-Page-Cache', 'HIT')->getContent();

        $this->assertSame($built, $stored);
    }

    public function test_admin_changes_show_at_once(): void
    {
        $url = $this->product->url();
        $this->get($url);
        $this->get($url)->assertHeader('X-Page-Cache', 'HIT');

        $this->product->update(['price' => 77]);
        $this->get($url)->assertHeader('X-Page-Cache', 'MISS')->assertSee('$77');

        Review::create(['product_id' => $this->product->id, 'name' => 'Sam', 'rating' => 5, 'message' => 'Great stuff']);
        $this->get($url)->assertHeader('X-Page-Cache', 'MISS')->assertSee('Great stuff');

        $this->get('/home');
        SpecialOffer::create(['product_id' => $this->product->id, 'position' => 1]);
        $this->assertSame('MISS', $this->cacheStatus('/home'));

        // Reordering pictures is one bulk update, which refreshes the pages itself
        $this->get($url);
        $image = ProductImage::where('product_id', $this->product->id)->first();
        $this->actingAs(User::factory()->create())
            ->postJson(route('productImage.reorder', $this->product->id), ['ids' => [$image->id]])->assertOk();
        auth()->logout();
        $this->assertSame('MISS', $this->cacheStatus($url));

        // Archiving takes the page down: archived products redirect, never a stored page
        $this->product->delete();
        $this->get($url)->assertRedirect();
    }

    public function test_pages_that_change_per_visitor_or_per_search_are_never_stored(): void
    {
        foreach (['/cart', '/checkout', '/?q=whey', '/?shop-search=whey', '/?min=1&max=50', '/?sale=1',
            '/?brand=no-such-brand', '/?fbclid=abc', '/?page=abc', '/shop/no-such-category', '/products/no-such-product',
            '/api/search?q=creatine'] as $url) {
            $this->get($url);
            $this->assertNull($this->cacheStatus($url), $url);
        }

        // The shop's own requests (filters, "load more") are built each time
        $this->get('/');
        $this->assertNull($this->cacheStatus('/', ['X-Requested-With' => 'XMLHttpRequest']));

        // A signed-in admin always sees the page built fresh
        $this->actingAs(User::factory()->create());
        $this->assertNull($this->cacheStatus('/'));
    }

    public function test_pages_fit_the_live_sites_database_cache(): void
    {
        // The live site keeps its cache in a table (CACHE_STORE=database) whose text column refuses raw
        // binary on MariaDB/MySQL: what is stored must be plain text
        config(['cache.default' => 'database']);
        foreach (['/home', '/', $this->product->url()] as $url) {
            $this->get($url)->assertOk()->assertHeader('X-Page-Cache', 'MISS');
            $this->get($url)->assertOk()->assertHeader('X-Page-Cache', 'HIT');
        }

        // (Laravel itself base64-encodes binary for SQLite, the test database, but not for MariaDB/MySQL, so
        // the page entry is checked, not the table row)
        $keys = DB::table('cache')->where('key', 'like', '%store.page.%')->pluck('key');
        $this->assertCount(3, $keys);
        foreach ($keys as $key) {
            $entry = Cache::get(substr($key, strlen((string) config('cache.prefix'))));
            $this->assertTrue(mb_check_encoding(serialize($entry), 'UTF-8'), 'a stored page is not plain text');
        }
    }

    public function test_switched_off_nothing_is_stored(): void
    {
        config(['store.page_cache' => false]);
        $this->get('/');
        $this->assertNull($this->cacheStatus('/'));
    }
}
