<?php

namespace Tests\Feature\Store;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\Review;
use App\Models\SpecialOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// The redesigned shop: subcategories, brands, filters, quick view, cart totals and suggestions
class ShopRedesignTest extends TestCase
{
    use RefreshDatabase;

    private function catalogue(): array
    {
        $protein = Category::create(['name' => 'Protein', 'icon' => 'protein', 'sort_order' => 1]);
        $whey = Category::create(['name' => 'Whey Protein', 'parent_id' => $protein->id, 'icon' => 'whey']);
        $isolate = Category::create(['name' => 'Isolate', 'parent_id' => $protein->id]);
        $creatine = Category::create(['name' => 'Creatine', 'icon' => 'creatine', 'sort_order' => 2]);
        $empty = Category::create(['name' => 'Empty Category', 'sort_order' => 3]);
        $on = Brand::create(['name' => 'Optimum Nutrition', 'logo' => 'storage/brands/on.webp']);
        $bio = Brand::create(['name' => 'BioTechUSA']);

        $products = [
            'gold' => Product::factory()->withImage()->create(['name' => 'Gold Standard', 'price' => 100, 'category_id' => $whey->id, 'brand_id' => $on->id]),
            'iso' => Product::factory()->withImage()->create(['name' => 'Iso Zero', 'price' => 120, 'discount' => 25, 'category_id' => $isolate->id, 'brand_id' => $bio->id]),
            'plain' => Product::factory()->withImage()->create(['name' => 'Plain Protein', 'price' => 50, 'category_id' => $protein->id]),
            'mono' => Product::factory()->withImage()->create(['name' => 'Mono Creatine', 'price' => 30, 'category_id' => $creatine->id, 'brand_id' => $bio->id]),
            'sold' => Product::factory()->soldOut()->withImage()->create(['name' => 'Sold Creatine', 'price' => 40, 'category_id' => $creatine->id]),
        ];

        return compact('protein', 'whey', 'isolate', 'creatine', 'empty', 'on', 'bio') + ['p' => $products];
    }

    private function ids($response): array
    {
        return $response->viewData('products')->pluck('id')->sort()->values()->all();
    }

    private function sorted(array $ids): array
    {
        sort($ids);

        return $ids;
    }

    public function test_main_category_lists_its_subcategories_and_the_menu_hides_empty_ones(): void
    {
        $c = $this->catalogue();
        $p = $c['p'];

        $main = $this->get($c['protein']->url())->assertOk();
        $this->assertSame($this->sorted([$p['gold']->id, $p['iso']->id, $p['plain']->id]), $this->ids($main));
        $main->assertSee('All Protein')->assertSee('Whey Protein')->assertSee('Isolate')->assertSee('ss-cat__icon', false);
        $this->assertNotContains('Empty Category', $main->viewData('tree')->pluck('name')->all(), 'categories without products are not shown');

        $sub = $this->get($c['whey']->url())->assertOk();
        $this->assertSame([$p['gold']->id], $this->ids($sub));
        $this->assertSame($c['protein']->id, $sub->viewData('main')->id, 'the subcategory page highlights its main category');
        $this->assertStringContainsString('"name":"Protein"', $sub->getContent(), 'breadcrumbs include the main category');
    }

    public function test_brand_price_stock_and_sale_filters(): void
    {
        $c = $this->catalogue();
        $p = $c['p'];

        $this->assertSame($this->sorted([$p['iso']->id, $p['mono']->id]), $this->ids($this->get('/shop?brand=' . $c['bio']->slug)));
        $this->assertSame($this->sorted([$p['gold']->id, $p['iso']->id, $p['mono']->id]),
            $this->ids($this->get('/shop?brand=' . $c['bio']->slug . ',' . $c['on']->slug)), 'several brands at once');
        $this->assertSame([$p['mono']->id], $this->ids($this->get($c['creatine']->url() . '?brand=' . $c['bio']->slug)), 'brand inside a category');

        // Iso Zero costs 120 - 25% = 90 after the discount
        $this->assertSame($this->sorted([$p['iso']->id, $p['gold']->id]), $this->ids($this->get('/shop?min=80&max=100')));
        $this->assertSame([$p['iso']->id], $this->ids($this->get('/shop?sale=1')));
        $this->assertNotContains($p['sold']->id, $this->ids($this->get('/shop?in_stock=1')));

        $response = $this->get('/shop?brand=' . $c['bio']->slug)->assertOk();
        $response->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->assertStringContainsString('<link rel="canonical" href="' . route('shop') . '">', $response->getContent());

        // brand buttons count the products of the current category, whatever brand is chosen
        $counts = $this->get($c['creatine']->url() . '?brand=' . $c['bio']->slug)->viewData('brandCounts');
        $this->assertSame([$c['bio']->id => 1], $counts->all());
    }

    public function test_searching_finds_a_brand_and_sorting_options(): void
    {
        $c = $this->catalogue();
        $p = $c['p'];

        $this->assertSame([$p['gold']->id], $this->ids($this->get('/shop?q=optimum')));
        $names = $this->get('/shop?orderby=name')->viewData('products')->pluck('name')->all();
        $this->assertSame(['Gold Standard', 'Iso Zero', 'Mono Creatine', 'Plain Protein', 'Sold Creatine'], $names);
        $this->assertSame($p['sold']->id, $this->get('/shop')->viewData('products')->last()->id, 'sold out products come last');
    }

    public function test_filters_load_without_reloading_the_page(): void
    {
        $c = $this->catalogue();
        Product::factory()->count(30)->withImage()->create(['category_id' => $c['creatine']->id]);

        $json = $this->getJson($c['creatine']->url() . '?orderby=newest', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json();
        foreach (['title', 'nav', 'sidebar', 'results', 'cards', 'next', 'total'] as $key) {
            $this->assertArrayHasKey($key, $json);
        }
        $this->assertSame(32, $json['total']);
        $this->assertSame($c['creatine']->url() . '?orderby=newest&page=2', $json['next']);
        $this->assertStringContainsString('data-quick=', $json['cards']);

        $page2 = $this->getJson($json['next'], ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json();
        $this->assertNull($page2['next']);
        $this->assertSame(32, $page2['to']);
    }

    public function test_quick_view_has_the_options_with_their_pictures_and_stock(): void
    {
        $product = Product::factory()->withImage()->withOptions('Flavor', ['Vanilla' => true, 'Mango' => false])->create(['price' => 80, 'discount' => 10]);
        ProductOption::where('option_value', 'Vanilla')->update(['image' => 'storage/product_options/vanilla.webp']);

        $json = $this->getJson("/products/{$product->id}/quick")->assertOk()->json();
        $this->assertSame(72.0, (float) $json['final_price']);
        $this->assertSame('Flavor', $json['options'][0]['name']);
        $this->assertSame([
            ['value' => 'Vanilla', 'in_stock' => true, 'image' => asset('storage/product_options/vanilla.webp')],
            ['value' => 'Mango', 'in_stock' => false, 'image' => null],
        ], $json['options'][0]['values']);
        $this->getJson('/products/999999/quick')->assertNotFound();
    }

    public function test_cart_totals_come_from_the_server_with_the_coupon(): void
    {
        $product = Product::factory()->withImage()->create(['price' => 50]);
        $this->addToCart($product, [], 2)->assertOk();

        $summary = $this->getJson('/api/cart')->assertOk()->json('summary');
        $this->assertSame(['count' => 2, 'subtotal' => 100, 'discount' => 0, 'total' => 100], array_intersect_key($summary, array_flip(['count', 'subtotal', 'discount', 'total'])));

        $coupon = Coupon::factory()->create(['code' => 'SAVE10', 'discount_percentage' => 10, 'minimum_order_amount' => 80]);
        $this->postJson('/coupon/apply', ['coupon_code' => 'save10'])->assertOk();
        $summary = $this->getJson('/api/cart')->json('summary');
        $this->assertEquals(10, $summary['discount']);
        $this->assertEquals(90, $summary['total']);
        $this->assertSame('SAVE10', $summary['coupon']['code']);

        // Below the coupon's minimum the discount is not counted, and the cart says why
        $line = $this->getJson('/api/cart')->json('items.0.id');
        $this->postJson('/cart/update', ['cart_item_id' => $line, 'quantity' => 1])->assertOk();
        $summary = $this->getJson('/api/cart')->json('summary');
        $this->assertEquals(0, $summary['discount']);
        $this->assertEquals(50, $summary['total']);
        $this->assertStringContainsString('minimum', $summary['coupon']['problem']);
        $this->assertNotNull($coupon);
    }

    public function test_an_empty_cart_has_a_summary(): void
    {
        $this->getJson('/api/cart')->assertOk()->assertJsonPath('summary.count', 0)->assertJsonPath('summary.total', 0);
    }

    public function test_suggestions_go_with_the_cart_and_skip_what_is_in_it(): void
    {
        $c = $this->catalogue();
        $p = $c['p'];
        $this->addToCart($p['gold'])->assertOk();

        $suggested = collect($this->getJson('/api/cart/suggestions')->assertOk()->json('products'))->pluck('id');
        $this->assertTrue($suggested->contains($p['mono']->id), 'protein in the cart suggests creatine');
        $this->assertFalse($suggested->contains($p['gold']->id), 'not what is already in the cart');
        $this->assertFalse($suggested->contains($p['sold']->id), 'nothing sold out');
    }

    public function test_the_chosen_flavour_picture_shows_in_the_cart_and_the_order(): void
    {
        $product = Product::factory()->withImage()->withOptions('Flavor', ['Vanilla' => true, 'Chocolate' => true])->create();
        ProductOption::where('option_value', 'Vanilla')->update(['image' => 'storage/product_options/vanilla.webp']);

        $this->addToCart($product, ['Flavor' => 'Vanilla'])->assertOk();
        $this->addToCart($product, ['Flavor' => 'Chocolate'])->assertOk();
        $images = collect($this->getJson('/api/cart')->json('items'))->pluck('product_image', 'selected_options');
        $this->assertSame('storage/product_options/vanilla.webp', $images['{"Flavor":"Vanilla"}']);
        $this->assertSame($product->images->first()->url, $images['{"Flavor":"Chocolate"}'], 'no picture: the product picture');

        $orderId = $this->placeOrder()->assertOk()->json('order_id');
        $this->assertContains('storage/product_options/vanilla.webp', OrderItem::where('order_id', $orderId)->pluck('product_image')->all());
    }

    public function test_product_page_shows_the_brand_and_option_pictures(): void
    {
        $brand = Brand::create(['name' => 'Optimum Nutrition']);
        $product = Product::factory()->withImage()->withOptions('Flavor', ['Vanilla' => true])->create(['brand_id' => $brand->id]);
        ProductOption::where('option_value', 'Vanilla')->update(['image' => 'storage/product_options/vanilla.webp']);

        $html = $this->get($product->url())->assertOk()->getContent();
        $this->assertStringContainsString('data-image="' . asset('storage/product_options/vanilla.webp') . '"', $html);
        $this->assertStringContainsString('"brand":{"@type":"Brand","name":"Optimum Nutrition"}', $html, 'structured data names the brand');
        $this->assertStringContainsString('/shop?brand=optimum-nutrition', $html);
    }

    public function test_cart_and_checkout_pages_use_the_new_layout(): void
    {
        $this->get('/cart')->assertOk()->assertSee('data-cart-lines', false)->assertSee('data-coupon', false)->assertSee('ss-tabbar', false);
        $this->get('/checkout')->assertOk()->assertSee('name="billing_phone"', false)->assertSee('name="checkout_token"', false)
            ->assertSee('data-order-done', false);
    }
    public function test_the_header_menu_has_shop_categories_with_subcategories_and_brands_but_no_plans(): void
    {
        $c = $this->catalogue();

        $home = $this->get('/')->assertOk();
        $home->assertSee('ss-topnav', false)
            ->assertSee('>Shop</a>', false)
            ->assertDontSee('>Products<', false)
            ->assertDontSee('>Plans<', false)->assertDontSee('>plans<', false)
            ->assertDontSee('#pricing"', false)
            ->assertSee('href="' . $c['whey']->url() . '" class="ss-mega__sub"', false)
            ->assertSee('href="' . route('shop', ['brand' => $c['on']->slug]) . '"', false)
            ->assertSee('storage/brands/on.webp', false)
            ->assertSee('ss-mega__initials">BI<', false);

        // The current page is marked
        $this->get('/shop')->assertOk()->assertSee('class="ss-topnav__link is-active">Shop</a>', false);
    }
    public function test_home_lists_every_brand_and_main_category_and_puts_sold_out_products_last(): void
    {
        $c = $this->catalogue();
        SpecialOffer::create(['product_id' => $c['p']['sold']->id, 'position' => 1]);
        SpecialOffer::create(['product_id' => $c['p']['gold']->id, 'position' => 2]);

        $home = $this->get('/')->assertOk();
        foreach ([$c['on'], $c['bio']] as $brand) {
            $home->assertSee('href="' . route('shop', ['brand' => $brand->slug]) . '" class="ss-hbrand"', false);
        }
        $home->assertSee('href="' . $c['protein']->url() . '" class="ss-hcat"', false)
            ->assertSee('Isolate, Whey Protein')            // its subcategories
            ->assertSee('ss-app', false)                     // phones get the app header and tabs here too
            ->assertDontSee('xb-item--cart-btn', false);
        $this->assertSame([$c['p']['gold']->id, $c['p']['sold']->id], $home->viewData('offersProducts')->pluck('id')->all(), 'sold out goes last');
        $this->assertFalse((bool) $home->viewData('product')->last()->stock || $home->viewData('product')->every(fn ($p) => $p->stock));
    }

    public function test_product_page_shows_rating_options_related_cards_and_the_phone_buy_bar(): void
    {
        $c = $this->catalogue();
        $gold = $c['p']['gold'];
        $sibling = Product::factory()->withImage()->create(['category_id' => $c['whey']->id]);
        Review::create(['product_id' => $gold->id, 'name' => 'Karim', 'message' => 'Mixes well', 'rating' => 4]);
        Review::create(['product_id' => $gold->id, 'name' => 'Lea', 'message' => 'Great taste', 'rating' => 5]);

        $this->get($gold->url())->assertOk()
            ->assertSee('<h1>Gold Standard</h1>', false)
            ->assertSee('style="--rating: 4.5"', false)
            ->assertSee('2 reviews')
            ->assertSee('Mixes well')
            ->assertSee('form="add-to-cart-form"', false)      // the bar at the bottom on phones
            ->assertSee('href="' . route('shop', ['brand' => $c['on']->slug]) . '"', false)
            ->assertSee('You may also like')
            ->assertSee('data-quick="' . $sibling->id . '"', false);

        // No reviews: empty stars, not five
        $this->get($c['p']['mono']->url())->assertOk()->assertSee('Be the first to review')->assertSee('style="--rating: 0"', false);
    }

    public function test_unknown_pages_get_the_store_not_found_page(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('We could not find this page')->assertSee('ss-topnav', false);
        $this->get('/products/no-such-product')->assertNotFound()->assertSee('Go to the shop');
    }

    public function test_empty_categories_are_never_shown_and_their_page_opens_the_shop(): void
    {
        $c = $this->catalogue();
        // An empty subcategory of a category that has products
        $shirts = Category::create(['name' => 'Shirts', 'parent_id' => $c['creatine']->id]);

        foreach (['/', '/shop', $c['creatine']->url(), '/cart', $c['p']['gold']->url()] as $page) {
            $html = $this->get($page)->assertOk()->getContent();
            $this->assertStringNotContainsString($c['empty']->url() . '"', $html, $page);
            $this->assertStringNotContainsString($shirts->url() . '"', $html, $page);
            $this->assertStringContainsString($c['whey']->url() . '"', $html, $page);
            // The header has no Contact link (the footer is the contact section)
            $this->assertStringNotContainsString('>Contact<', $html, $page);
        }

        $this->get($c['empty']->url())->assertRedirect(route('shop'));
        $this->get($shirts->url())->assertRedirect(route('shop'));

        // Once it has a product it shows again
        Product::factory()->withImage()->create(['name' => 'Gym Shirt', 'category_id' => $shirts->id]);
        $this->get($shirts->url())->assertOk();
        $this->assertStringContainsString($shirts->url() . '"', $this->get('/shop')->getContent());
    }
}
