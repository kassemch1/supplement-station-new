<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Exercises every write route against the local DB copy inside a transaction that is rolled back.
class WriteFlowsTest extends TestCase
{
    use DatabaseTransactions;

    private array $results = [];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
        // uploads go to the public disk since Phase 2 (and old files may be deleted), never touch the real one
        Storage::fake('public');
    }

    private function check(string $label, $res, array $okCodes = [200, 201, 302]): void
    {
        $code = $res->getStatusCode();
        $ex = $res->baseResponse->exception ?? null;
        $errors = session('errors');
        $problem = '';
        if ($ex && !($ex instanceof \Illuminate\Validation\ValidationException)) {
            $problem = class_basename($ex).': '.substr($ex->getMessage(), 0, 200).' @ '.basename($ex->getFile()).':'.$ex->getLine();
        } elseif ($errors && $errors->any()) {
            $problem = 'validation: '.implode(' | ', $errors->all());
        } elseif (!in_array($code, $okCodes)) {
            $problem = 'body: '.substr(strip_tags($res->getContent()), 0, 200);
        }
        $ok = in_array($code, $okCodes) && $problem === '';
        $this->results[] = [$ok, $code, $label, $problem];
        session()->forget('errors');
    }

    private function report(): void
    {
        $fails = 0;
        foreach ($this->results as [$ok, $code, $label, $problem]) {
            if (!$ok) $fails++;
            fwrite(STDOUT, ($ok ? 'OK   ' : 'FAIL ').str_pad($code, 4).$label.($problem ? "  -> $problem" : '')."\n");
        }
        fwrite(STDOUT, "== failures: $fails / ".count($this->results)."\n");
        $this->assertSame(0, $fails);
    }

    public function test_customer_flows(): void
    {
        // A product with options, and pick one in-stock value per option type
        $productId = DB::table('product_options')->where('stock', 1)->value('product_id');
        $selected = DB::table('product_options')->join('options', 'options.id', '=', 'product_options.option_id')
            ->where('product_id', $productId)->where('stock', 1)->get()
            ->groupBy('option_name')->map(fn ($g) => $g->first()->option_value)->all();
        $plainProduct = DB::table('products')->whereNotIn('id', DB::table('product_options')->pluck('product_id'))->value('id');

        $this->check("cart/add product $productId with options", $this->postJson('/cart/add', [
            'product_id' => $productId, 'quantity' => 2, 'selected_options' => json_encode($selected),
        ]));
        $this->check('cart/add same item again (merge)', $this->postJson('/cart/add', [
            'product_id' => $productId, 'quantity' => 1, 'selected_options' => json_encode($selected),
        ]));
        if ($plainProduct) {
            $this->check("cart/add product $plainProduct without options", $this->postJson('/cart/add', [
                'product_id' => $plainProduct, 'quantity' => 1, 'selected_options' => '{}',
            ]));
        }
        $cart = $this->getJson('/api/cart');
        $this->check('api/cart lists items', $cart);
        $this->results[] = [count($cart->json('items')) >= 1, 200, 'cart has '.count($cart->json('items')).' item(s), first qty='.($cart->json('items.0.quantity')), ''];

        $this->check('cart/update quantity', $this->postJson('/cart/update', [
            'product_id' => $productId, 'quantity' => 4, 'selected_options' => json_encode($selected),
        ]));
        if ($plainProduct) {
            $this->check('cart/remove item', $this->postJson('/cart/remove', ['product_id' => $plainProduct, 'selected_options' => '{}']));
        }

        // Coupon: make a valid one for the test (rolled back afterwards)
        DB::table('coupons')->insert(['code' => 'UPGRADETEST', 'name' => 'Upgrade test', 'type' => 'reusable',
            'discount_percentage' => 10, 'minimum_order_amount' => 0, 'used_count' => 0, 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        $this->check('coupon/apply', $this->postJson('/coupon/apply', ['coupon_code' => 'upgradetest']));
        $this->check('coupon/applied', $this->getJson('/coupon/applied'));
        $this->check('coupon/remove', $this->postJson('/coupon/remove'));
        $this->check('coupon/apply again for order', $this->postJson('/coupon/apply', ['coupon_code' => 'UPGRADETEST']));
        $this->check('coupon/apply invalid code is rejected cleanly', $this->postJson('/coupon/apply', ['coupon_code' => 'NOPE-XYZ']), [200, 400, 404, 422]);

        $ordersBefore = DB::table('orders')->count();
        $order = $this->postJson('/place-order', [
            'billing_first_name' => 'Upgrade Test', 'billing_phone' => '03123456', 'billing_address_1' => 'Hamra St',
            'billing_city' => 'Beirut', 'billing_email' => 'upgrade-test@example.com',
        ]);
        $this->check('place-order', $order);
        $orderId = $order->json('order_id');
        $o = DB::table('orders')->find($orderId);
        $this->results[] = [(bool) $o && DB::table('orders')->count() === $ordersBefore + 1, 200,
            "order #$orderId saved: subtotal={$o?->subtotal_amount} discount={$o?->coupon_discount_amount} total={$o?->total_amount}", ''];
        $this->results[] = [DB::table('order_items')->where('order_id', $orderId)->sum('quantity') == 4, 200, 'order items qty = 4', ''];
        $this->results[] = [DB::table('shipping_details')->where('order_id', $orderId)->exists(), 200, 'shipping details saved', ''];
        $sent = Mail::sent(\App\Mail\CheckoutMail::class)->count();
        $this->results[] = [$sent === 1, 200, "checkout email built and sent (faked): $sent", ''];
        // Render the email body too, since mail views run through the upgraded framework
        Mail::sent(\App\Mail\CheckoutMail::class)->each(fn ($m) => $m->render());
        $this->results[] = [true, 200, 'checkout email renders', ''];
        $this->check('cart empty after order', $this->getJson('/api/cart'));
        $this->results[] = [count($this->getJson('/api/cart')->json('items')) === 0, 200, 'cart emptied', ''];

        $this->check('place-order with empty cart is rejected with a message', $this->postJson('/place-order', [
            'billing_first_name' => 'X', 'billing_phone' => '03123456', 'billing_address_1' => 'X', 'billing_city' => 'X',
        ]), [422]);
        $this->check('place-order with bad phone is rejected', $this->postJson('/place-order', [
            'billing_first_name' => 'X', 'billing_phone' => '123', 'billing_address_1' => 'X', 'billing_city' => 'X',
        ]), [422]);

        $this->check('product review', $this->post("/product/$productId/review", ['name' => 'Tester', 'message' => 'Great', 'rating' => 5]));
        $this->check('contact form', $this->post('/contact/send', ['name' => 'Tester', 'email' => 't@example.com', 'phone' => '03123456', 'message' => 'Hi']));
        $this->check('subscribe', $this->post('/subscribe', ['email' => 'upgrade-sub@example.com']));
        $this->check('subscription/cancel', $this->post('/subscription/cancel', ['email' => 'upgrade-sub@example.com']));

        $this->report();
    }

    public function test_admin_flows(): void
    {
        $this->actingAs(User::first());
        $cat = DB::table('categories')->value('id');
        $img = fn ($n) => UploadedFile::fake()->image($n, 600, 600);

        // Products
        $this->check('product store with 2 images', $this->post('/manageProducts/store', [
            'name' => 'Upgrade Test Product', 'price' => 50, 'category_id' => $cat, 'discount' => 10,
            'description' => 'test', 'stock' => 'in-stock', 'images' => [$img('a.png'), $img('b.jpg')],
        ]));
        $pid = DB::table('products')->where('name', 'Upgrade Test Product')->value('id');
        $this->results[] = [(bool) $pid && DB::table('product_images')->where('product_id', $pid)->count() === 2, 200, "product #$pid created with 2 image rows", ''];
        $url = DB::table('product_images')->where('product_id', $pid)->value('url');
        $this->results[] = [$url && Storage::disk('public')->exists(str_replace('storage/', '', $url)), 200, "image file stored at $url", ''];
        $this->check('product update + new image', $this->post('/manageProducts/update', [
            'product_id' => $pid, 'name' => 'Upgrade Test Product 2', 'price' => 60, 'category_id' => $cat, 'discount' => 0,
            'description' => 'test2', 'stock' => 'out-of-stock', 'images' => [$img('c.png')],
        ]));
        $p = DB::table('products')->find($pid);
        $this->results[] = [$p->price == 60 && $p->stock == 0 && $p->name === 'Upgrade Test Product 2', 200, 'product update saved', ''];
        $this->check('product page shows new product', $this->get($this->productUrl($pid)));
        $imageId = DB::table('product_images')->where('product_id', $pid)->value('id');
        $this->check('product image destroy', $this->delete("/productImage/$imageId"));

        // Options and product options
        $this->check('option store', $this->post('/manageOptions/store', ['option_name' => 'UpgradeTestOpt']));
        $oid = DB::table('options')->where('option_name', 'UpgradeTestOpt')->value('id');
        $this->check('option update', $this->put('/manageOptions/update', ['option_id' => $oid, 'option_name' => 'UpgradeTestOpt2']));
        $this->check('product option store', $this->post('/manageProductsOptions/store', ['product_id' => $pid, 'option_id' => $oid, 'option_value' => 'Big', 'stock' => 1]));
        $poid = DB::table('product_options')->where('product_id', $pid)->value('id');
        $this->check('product option update (sold out)', $this->put("/manageProductsOptions/$poid", ['id' => $poid, 'option_id' => $oid, 'option_value' => 'Bigger', 'stock' => 0]));
        $this->results[] = [DB::table('product_options')->where('id', $poid)->value('stock') == 0, 200, 'option marked sold out', ''];
        $this->check('product option destroy', $this->delete("/manageProductsOptions/$poid", ['id' => $poid]));
        $this->check('option destroy', $this->delete('/manageOptions/destroy', ['option_id' => $oid]));

        // Categories
        $this->check('category store', $this->post('/manageCategories/store', ['name' => 'UpgradeTestCat']));
        $cid = DB::table('categories')->where('name', 'UpgradeTestCat')->value('id');
        $this->check('category update', $this->put('/manageCategories/update', ['category_id' => $cid, 'name' => 'UpgradeTestCat2']));
        $this->check('category destroy', $this->delete('/manageCategories/destroy', ['id' => $cid]));

        // FAQ
        $this->check('faq store', $this->post('/manageFaq/store', ['question' => 'UpgradeQ?', 'answer' => 'A']));
        $fid = DB::table('faqs')->where('question', 'UpgradeQ?')->value('id');
        $this->check('faq update', $this->put('/manageFaq/update', ['faqId' => $fid, 'question' => 'UpgradeQ2?', 'answer' => 'B']));
        $this->check('faq destroy', $this->delete('/manageFaq/destroy', ['id' => $fid]));

        // Banner (PNG, and SVG must be rejected)
        $this->check('banner store png', $this->post('/manageBanner/store', ['image' => $img('banner.png'), 'product_id' => $pid]));
        $svg = UploadedFile::fake()->createWithContent('banner.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>');
        // Phase 2: SVG is no longer accepted (it can carry scripts)
        $res = $this->post('/manageBanner/store', ['image' => $svg, 'product_id' => $pid]);
        $this->results[] = [$res->getStatusCode() === 302 && session('errors')?->has('image'), $res->getStatusCode(), 'banner store svg is rejected', ''];
        session()->forget('errors');

        // Special offers
        DB::table('special_offers')->orderByDesc('position')->limit(1)->delete();
        $this->check('special offer store', $this->post('/admin/special-offers', ['product_id' => $pid]));
        $soid = DB::table('special_offers')->where('product_id', $pid)->value('id');
        $this->check('special offer update', $this->put("/admin/special-offers/$soid", ['position' => 1]));
        $this->check('special offer destroy', $this->delete("/admin/special-offers/$soid"));

        // Coupons
        $this->check('coupon store', $this->post('/admin/coupons', ['code' => 'upgadmin', 'name' => 'Admin test', 'description' => '', 'type' => 'single_use',
            'discount_percentage' => 5, 'minimum_order_amount' => 0, 'max_uses' => 3, 'expires_at' => now()->addMonth()->toDateString()]));
        $cpid = DB::table('coupons')->where('code', 'UPGADMIN')->value('id');
        $this->check('coupon update', $this->put("/admin/coupons/$cpid", ['name' => 'Admin test 2', 'description' => '', 'type' => 'reusable',
            'discount_percentage' => 7, 'minimum_order_amount' => 10, 'is_active' => 1]));
        $this->check('coupon toggle', $this->patch("/admin/coupons/$cpid/toggle-status"));
        $this->check('coupon destroy', $this->delete("/admin/coupons/$cpid"));

        // Orders
        $orderId = DB::table('orders')->where('status', 'pending')->value('id') ?? DB::table('orders')->value('id');
        $this->check('order status -> delivered', $this->post("/admin/orders/$orderId/update-status", ['status' => 'delivered']));
        $this->results[] = [DB::table('orders')->where('id', $orderId)->value('status') === 'delivered', 200, 'order status saved', ''];
        $this->check('order delete', $this->delete("/admin/orders/$orderId"));

        // Reviews
        $rid = DB::table('reviews')->value('id');
        if ($rid) $this->check('review destroy', $this->deleteJson("/manageProductReview/$rid"));

        // Subscribers
        $this->check('subscriber store', $this->post('/admin/subscribers', ['email' => 'upg-admin-sub@example.com']));
        $sid = DB::table('subscriptions')->where('email', 'upg-admin-sub@example.com')->value('id');
        $this->check('subscriber update', $this->put("/admin/subscribers/$sid", ['email' => 'upg-admin-sub2@example.com']));
        $this->check('subscriber toggle', $this->post("/admin/subscribers/$sid/toggle-status"));
        $this->check('subscriber bulk deactivate', $this->post('/admin/subscribers/bulk-action', ['action' => 'deactivate', 'subscriber_ids' => [$sid]]));
        $this->check('subscriber destroy', $this->delete("/admin/subscribers/$sid"));

        // Product email to subscribers (faked mail)
        $this->check('product email to subscribers', $this->post('/admin/products/send-email', ['product_id' => DB::table('products')->value('id')]));

        // Product delete last
        $this->check('product destroy', $this->delete('/manageProducts/destroy', ['id' => $pid]));

        // Plans (known pre-existing bug: App\Models\plane)
        $this->check('plan store', $this->post('/managePlan/store', ['type' => 'Upgrade', 'price' => 10, 'point1' => 'A', 'point2' => '', 'point3' => '', 'point4' => '', 'point5' => '']));
        $plid = DB::table('plans')->where('type', 'Upgrade')->value('id');
        $this->check('plan list', $this->get('/managePlan'));
        $this->check('plan edit page', $this->get("/managePlan/$plid/edit"));
        $this->check('plan update', $this->put('/managePlan/update', ['planId' => $plid, 'type' => 'Upgrade2', 'price' => 12, 'point1' => 'B', 'point2' => '', 'point3' => '', 'point4' => '', 'point5' => '']));
        $this->results[] = [DB::table('plans')->where('id', $plid)->value('price') == 12, 200, 'plan update saved', ''];
        $this->check('home shows plans', $this->get('/'));
        $this->check('plan destroy', $this->delete('/managePlan/destroy', ['id' => $plid]));

        // Auth
        $this->check('logout', $this->post('/logout'));
        $r = $this->get('/admin'); $this->results[] = [$r->isRedirect(url('/login')), $r->getStatusCode(), 'admin redirects to login when logged out', ''];
        $r = $this->post('/auth', ['email' => 'nobody@example.com', 'password' => 'wrong']); $this->results[] = [$r->isRedirect() && session('errors')?->has('email') && !auth()->check(), $r->getStatusCode(), 'login with wrong password is rejected', '']; session()->forget('errors');

        $this->report();
    }
}
