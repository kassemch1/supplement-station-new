<?php

namespace Tests\Feature\Store;

use App\Models\Category;
use App\Models\Product;
use App\Services\IndexNow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// The category "About" text and IndexNow (telling Bing about changed pages)
class SeoExtrasTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'test-indexnow-key-1234';

    public function test_the_admin_writes_a_category_text_that_shows_under_its_products(): void
    {
        $this->admin();
        $category = Category::create(['name' => 'Creatine']);
        Product::factory()->withImage()->create(['category_id' => $category->id]);

        $this->putJson('/manageCategories/update', [
            'category_id' => $category->id, 'name' => 'Creatine',
            'description' => "Creatine helps strength.\n\nTake 5 grams a day <b>with water</b>.",
        ])->assertJson(['status' => 'success']);
        $this->get('/manageCategories/' . $category->id . '/edit')->assertOk()->assertSee('Creatine helps strength.');
        $this->postJson('/manageCategories/store', ['name' => 'Long', 'description' => str_repeat('a', 3001)])->assertJsonValidationErrors('description');

        auth()->logout();
        $html = $this->get('/shop/creatine')->assertOk()->getContent();
        $this->assertStringContainsString('<h2>About Creatine</h2>', $html);
        $this->assertStringContainsString('<p>Creatine helps strength.</p>', $html);
        $this->assertStringContainsString('<p>Take 5 grams a day &lt;b&gt;with water&lt;/b&gt;.</p>', $html, 'shown as text, never as HTML');
        $this->assertStringContainsString('<meta name="description" content="Creatine helps strength. Take 5 grams a day with water.">', $html);

        // Not on page 2 or on search results; a category without a text keeps the short generated line
        $this->assertStringNotContainsString('About Creatine', $this->get('/shop/creatine?q=zzz')->getContent());
        $other = Category::create(['name' => 'Vitamins']);
        Product::factory()->withImage()->create(['category_id' => $other->id]);
        $html = $this->get('/shop/vitamins')->getContent();
        $this->assertStringNotContainsString('ss-shop__about', $html);
        $this->assertStringContainsString('ss-shop__intro', $html);
    }

    public function test_indexnow_is_off_without_a_key(): void
    {
        Http::fake();
        config(['store.indexnow_key' => null]);

        Product::factory()->withImage()->create();
        app()->terminate();

        Http::assertNothingSent();
        $this->get('/indexnow.txt')->assertNotFound();
        $this->artisan('seo:indexnow')->assertFailed();
    }

    public function test_a_changed_product_is_sent_once_at_the_end_of_the_request(): void
    {
        Http::fake(['api.indexnow.org/*' => Http::response('', 200)]);
        config(['store.indexnow_key' => self::KEY]);

        $this->get('/indexnow.txt')->assertOk()->assertSeeText(self::KEY);

        $product = Product::factory()->withImage()->create();
        $product->update(['price' => 50]);
        Http::assertNothingSent();   // nothing while the admin's page is still being answered
        app()->terminate();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.indexnow.org/indexnow'
            && $request['key'] === self::KEY
            && $request['keyLocation'] === url('/indexnow.txt')
            && collect($request['urlList'])->sort()->values()->all() === collect([$product->url(), $product->getCategory->url()])->sort()->values()->all());
    }

    public function test_a_search_engine_that_is_down_never_breaks_the_admin(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('down'));
        config(['store.indexnow_key' => self::KEY]);

        Product::factory()->withImage()->create();
        app()->terminate();

        $this->assertSame(0, IndexNow::submit(['https://example.com/x']));
    }

    public function test_the_command_sends_every_sitemap_address(): void
    {
        Http::fake(['api.indexnow.org/*' => Http::response('', 202)]);
        $product = Product::factory()->withImage()->create();
        config(['store.indexnow_key' => self::KEY]);

        $this->artisan('seo:indexnow')->expectsOutputToContain('Sent 4 of 4 addresses')->assertSuccessful();
        Http::assertSent(fn (Request $request) => $request['urlList'] === [
            url('/') . '/', route('home'), $product->getCategory->url(), $product->url(),
        ]);
    }
}
