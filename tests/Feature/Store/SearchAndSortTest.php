<?php

namespace Tests\Feature\Store;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Option;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductOption;
use App\Services\ProductSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// The search (typos, suggestions while typing), the shop's sorting and "load more", and the small
// store changes: the Best sellers tab, the contact number and plain flavour buttons
class SearchAndSortTest extends TestCase
{
    use RefreshDatabase;

    private function ids(string $url): array
    {
        return $this->get($url)->assertOk()->viewData('products')->pluck('id')->all();
    }

    private function catalogue(): array
    {
        $pre = Category::create(['name' => 'Pre-Workout']);
        $whey = Category::create(['name' => 'Whey Protein']);
        $creatine = Category::create(['name' => 'Creatine']);
        $on = Brand::create(['name' => 'Optimum Nutrition']);

        return [
            'gold' => Product::factory()->withImage()->create(['name' => 'Gold Standard 100% Whey', 'price' => 110, 'category_id' => $whey->id, 'brand_id' => $on->id]),
            'iso' => Product::factory()->withImage()->create(['name' => 'ISO 100', 'price' => 150, 'category_id' => $whey->id]),
            'c4' => Product::factory()->withImage()->create(['name' => 'C4 Original', 'price' => 45, 'category_id' => $pre->id]),
            'mono' => Product::factory()->withImage()->create(['name' => 'Creatine Monohydrate 300g', 'price' => 20, 'category_id' => $creatine->id]),
            'shaker' => Product::factory()->withImage()->create(['name' => 'Spider Shaker', 'price' => 6]),
        ];
    }

    // ---------- search ----------

    public function test_the_search_forgives_typos_spaces_and_finds_brands_and_categories(): void
    {
        $p = $this->catalogue();

        $this->assertSame([$p['gold']->id], ProductSearch::ids('gold standart'), 'a wrong letter');
        $this->assertContains($p['gold']->id, ProductSearch::ids('protien'), 'swapped letters, found through its category');
        $this->assertSame([$p['mono']->id], ProductSearch::ids('creatin'), 'the start of a word');
        $this->assertSame([$p['iso']->id], ProductSearch::ids('iso100'), 'written together');
        $this->assertSame([$p['c4']->id], ProductSearch::ids('preworkout'), 'the category written together');
        $this->assertSame([$p['gold']->id], ProductSearch::ids('optimum'), 'the brand');
        $this->assertSame([], ProductSearch::ids('zzzz'));
        $this->assertSame([], ProductSearch::ids('300kg'), 'numbers must match exactly');
    }

    public function test_search_results_come_best_match_first_and_follow_new_products(): void
    {
        $p = $this->catalogue();
        $wheyBar = Product::factory()->withImage()->create(['name' => 'Whey Bar Cookies', 'price' => 3]);

        $this->assertSame([$wheyBar->id, $p['gold']->id, $p['iso']->id], $this->ids('/shop?q=whey'),
            'the name match first, then the whey category; a sold-out product would come last');

        $new = Product::factory()->withImage()->create(['name' => 'Whey Gold Isolate', 'price' => 80, 'discount' => 0]);
        $this->assertContains($new->id, $this->ids('/shop?q=whey'), 'a new product is found right away');
        $this->assertSame([$p['iso']->id, $p['gold']->id, $new->id, $wheyBar->id],
            array_values(array_intersect($this->ids('/shop?q=whey&orderby=high-to-low'), [$p['iso']->id, $p['gold']->id, $wheyBar->id, $new->id])),
            'another sort still applies to the results');
    }

    public function test_the_search_box_suggests_products_categories_and_brands(): void
    {
        $p = $this->catalogue();

        $json = $this->getJson('/api/search?q=optimun')->assertOk()->json();
        $this->assertSame([$p['gold']->id], array_column($json['products'], 'id'));
        $this->assertSame('Gold Standard 100% Whey', $json['products'][0]['name']);
        $this->assertSame(110.0, (float) $json['products'][0]['final_price']);
        $this->assertSame(['Optimum Nutrition'], array_column($json['brands'], 'name'));
        $this->assertSame(1, $json['total']);
        $this->assertSame(route('shop', ['q' => 'optimun']), $json['all']);

        $this->assertSame(['Creatine'], array_column($this->getJson('/api/search?q=creatine')->json('categories'), 'name'));
        $this->assertSame(0, $this->getJson('/api/search?q=c')->json('total'), 'one letter is too short');
    }

    public function test_mangled_links_show_the_page_instead_of_an_error(): void
    {
        $this->catalogue();

        foreach (['/shop?brand[]=x', '/shop?orderby[]=1', '/shop?q[]=a', '/shop?shop-search[]=a', '/shop?min[]=1', '/shop?max[]=2',
                     '/shop?in_stock[]=1', '/shop?sale[]=1', '/shop/creatine?brand[]=x&q[]=y', '/?q[]=1', '/cart?q[]=1'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->getJson('/api/search?q[]=x')->assertOk()->assertJsonPath('total', 0);
        $this->get('/shop?q=' . urlencode('whey"><script>'))->assertOk()->assertDontSee('"><script>', false);
    }

    // ---------- sorting and "load more" ----------

    public function test_every_sort_shows_products_in_stock_first(): void
    {
        $p = $this->catalogue();
        $cheapSoldOut = Product::factory()->soldOut()->withImage()->create(['name' => 'Sold Out Bar', 'price' => 1]);

        foreach (['default', 'best-selling', 'newest', 'low-to-high', 'high-to-low', 'name'] as $sort) {
            $this->assertSame($cheapSoldOut->id, last($this->ids("/shop?orderby={$sort}")), $sort);
        }
        $this->assertSame([$p['shaker']->id, $p['mono']->id, $p['c4']->id], array_slice($this->ids('/shop?orderby=low-to-high'), 0, 3));
    }

    public function test_best_selling_and_recommended_put_the_most_sold_products_first(): void
    {
        $p = $this->catalogue();
        OrderItem::factory()->create(['product_id' => $p['mono']->id, 'quantity' => 5, 'order_id' => Order::factory()->status('delivered')]);
        OrderItem::factory()->create(['product_id' => $p['c4']->id, 'quantity' => 2, 'order_id' => Order::factory()->status('delivered')]);
        // Not delivered: doesn't count
        OrderItem::factory()->create(['product_id' => $p['shaker']->id, 'quantity' => 50, 'order_id' => Order::factory()->status('cancelled')]);

        $this->assertSame([$p['mono']->id, $p['c4']->id], array_slice($this->ids('/shop?orderby=best-selling'), 0, 2));
        $this->assertSame([$p['mono']->id, $p['c4']->id], array_slice($this->ids('/shop'), 0, 2));
        $this->get('/shop')->assertSee('<option value="best-selling" >Best selling</option>', false);
    }

    public function test_load_more_never_repeats_or_skips_products_with_the_same_price(): void
    {
        Product::factory()->count(60)->withImage()->create(['price' => 10, 'discount' => 0]);

        foreach (['low-to-high', 'name', 'default'] as $sort) {
            $seen = [];
            $url = "/shop?orderby={$sort}";
            while ($url) {
                $json = $this->getJson($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json();
                preg_match_all('/data-quick="(\d+)"/', $json['cards'], $m);
                $this->assertLessThanOrEqual(24, count($m[1]), 'one page at a time');
                $seen = array_merge($seen, $m[1]);
                $url = $json['next'] ? parse_url($json['next'], PHP_URL_PATH) . '?' . parse_url($json['next'], PHP_URL_QUERY) : null;
            }
            $this->assertCount(60, $seen, $sort);
            $this->assertCount(60, array_unique($seen), $sort);
        }
    }

    // ---------- small store changes ----------

    public function test_the_phone_tab_bar_links_best_sellers_and_the_new_number(): void
    {
        $this->catalogue();
        $page = $this->get('/shop')->assertOk();

        $page->assertSee('href="' . route('shop', ['orderby' => 'best-selling']) . '"', false)
            ->assertSee('<span>Best sellers</span>', false)
            ->assertDontSee('<span>Deals</span>', false)
            ->assertSee('https://wa.me/96181088266', false)
            ->assertSee('+961 81 088 266');
        foreach (['81823038', '81 823 038', '81-823-038'] as $old) {
            $page->assertDontSee($old, false);
        }
        $this->get('/shop?orderby=best-selling')
            ->assertSee('href="' . route('shop', ['orderby' => 'best-selling']) . '" class="ss-tabbar__item is-active"', false);
    }

    public function test_flavour_buttons_show_only_the_flavour_name(): void
    {
        $p = $this->catalogue();
        $flavour = Option::firstOrCreate(['option_name' => 'Flavor']);
        ProductOption::create(['product_id' => $p['gold']->id, 'option_id' => $flavour->id, 'option_value' => 'Vanilla', 'stock' => 1, 'image' => 'storage/product_options/vanilla.webp']);

        $html = $this->get($p['gold']->url())->assertOk()->assertSee('Vanilla')->getContent();
        preg_match('#<button[^>]*data-option-value="Vanilla"[^>]*>(.*?)</button>#s', $html, $button);
        $this->assertNotEmpty($button);
        $this->assertStringNotContainsString('<img', $button[1]);
        $this->assertStringContainsString('data-image="', $button[0], 'choosing it still shows its picture in the gallery');
    }
}
