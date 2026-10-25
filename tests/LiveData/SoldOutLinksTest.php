<?php
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SoldOutLinksTest extends TestCase
{
    use DatabaseTransactions;

    private function linksTo(string $html, int $id): int
    {
        return substr_count($html, 'href="'.$this->productUrl($id).'"');
    }

    public function test_sold_out_cards_link_to_their_page_everywhere(): void
    {
        $sold = DB::table('products')->whereNull('deleted_at')->where('stock', 0)->first();
        $search = urlencode(substr($sold->name, 0, 20));

        $desktop = $this->get('/shop?shop-search='.$search)->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(3, $this->linksTo($desktop, $sold->id), 'desktop card: image, title and button link');
        $this->assertStringNotContainsString('showOutOfStockMessage(event)', $desktop);

        $mobile = $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile/15E148')
            ->get('/shop?shop-search='.$search)->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(3, $this->linksTo($mobile, $sold->id), 'mobile card: image, title and button link');

        // Home: make the sold-out product a special offer so it shows there
        DB::table('special_offers')->delete();
        DB::table('special_offers')->insert(['product_id' => $sold->id, 'position' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(3, $this->linksTo($home, $sold->id), 'home special offer card links');

        // Related products (up to 8, in stock first): a sold-out product of a small category is among them
        $small = DB::table('products')->whereNull('deleted_at')->select('category_id')->groupBy('category_id')
            ->havingRaw('count(*) between 2 and 9 and sum(stock = 0) > 0')->value('category_id');
        $soldRelated = DB::table('products')->whereNull('deleted_at')->where('category_id', $small)->where('stock', 0)->value('id');
        $sibling = DB::table('products')->whereNull('deleted_at')->where('category_id', $small)->where('id', '!=', $soldRelated)->value('id');
        $page = $this->get($this->productUrl($sibling))->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(3, $this->linksTo($page, $soldRelated), 'related products card links');

        $this->get($this->productUrl($sold->id))->assertOk()->assertSee('Sold out');
    }

    public function test_related_add_to_cart_button_points_at_the_related_product(): void
    {
        $item = DB::table('products as a')->join('products as b', fn ($j) => $j->on('a.category_id', '=', 'b.category_id')->on('a.id', '!=', 'b.id'))
            ->whereNull('a.deleted_at')->whereNull('b.deleted_at')->where('b.stock', 1)->select('a.id as page', 'b.id as related')->first();
        $response = $this->get($this->productUrl($item->page));
        // the slider shows up to 8 related products; check one that is shown and in stock
        $related = $response->viewData('relatedProducts')->firstWhere('stock', 1);
        $this->assertMatchesRegularExpression('#href="'.preg_quote($related->url(), '#').'" class="xb-item--cart-btn"#', $response->getContent());
    }
}
