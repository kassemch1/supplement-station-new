<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\PushSubscription;
use App\Models\Setting;
use App\Models\ShippingDetail;
use App\Services\AdminPush;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PushNotificationsTest extends TestCase
{
    use RefreshDatabase;

    // Made for these tests only (a real P-256 key pair, and a real device key), so no key has to be made here
    private const VAPID = ['publicKey' => 'BEY_pJq8w62EjaYG5OUsnk27FuR30JfhSuVJ2x7oEMlUSHStqx0-rmW5ssb8ruMtr17PZCE8S1l6dGagS-opzjE',
                           'privateKey' => 'E6fDEQBBVejCAl9FieryZEPSP4ehYrXE5O9IqqmpVoE'];
    private const DEVICE_KEY = 'BMvQe7FJCI2MA9vTCISF6GaD2KTpy_yNFSkbYdEgyLb5ChMREGGCPnEAIU7wdzVSd49CgOgHCQF0mA1yG6bg2m8';
    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148';

    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Setting::write(AdminPush::KEYS_SETTING, json_encode(self::VAPID));
    }

    private function subscribe(string $endpoint): \Illuminate\Testing\TestResponse
    {
        return $this->withHeader('User-Agent', self::IPHONE)->postJson('/admin/push/subscribe', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => self::DEVICE_KEY, 'auth' => 'x4Fh0Yp3l2qS9w1kJt7m8A'],
        ]);
    }

    // The push services answer with these responses, one per notification, without the network
    private function pushServiceAnswers(Response ...$responses): void
    {
        // sending encrypts each message with a new P-256 key; PHP on Windows needs OPENSSL_CONF for that
        if (@openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]) === false) {
            $this->markTestSkipped('OpenSSL cannot make P-256 keys here (on Windows, set OPENSSL_CONF to PHP\'s extras/ssl/openssl.cnf).');
        }
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->sent));
        $this->app->instance(AdminPush::class, new AdminPush(new Client(['handler' => $stack])));
    }

    public function test_a_device_turns_notifications_on_and_off(): void
    {
        $this->subscribe('https://web.push.apple.com/abc')->assertUnauthorized();
        $this->getJson('/admin/push/key')->assertUnauthorized();

        $this->admin();
        $this->getJson('/admin/push/key')->assertOk()->assertExactJson(['key' => self::VAPID['publicKey']]);
        $this->subscribe('https://web.push.apple.com/abc')->assertOk();
        $this->subscribe('https://web.push.apple.com/abc')->assertOk();    // again: the same device, once
        $device = PushSubscription::sole();
        $this->assertSame('iPhone', $device->device);
        $this->assertSame(self::DEVICE_KEY, $device->public_key);
        $this->get('/admin/settings')->assertOk()->assertSee('iPhone')->assertSee('data-ready="1"', false);

        $this->subscribe('http://example.com/not-a-push-service')->assertJsonValidationErrors('endpoint');

        $this->postJson('/admin/push/unsubscribe', ['endpoint' => 'https://web.push.apple.com/abc'])->assertOk();
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_a_new_order_notifies_every_device_and_forgets_the_ones_that_ended(): void
    {
        $this->admin();
        $this->subscribe('https://web.push.apple.com/iphone')->assertOk();
        $this->subscribe('https://fcm.googleapis.com/fcm/send/deleted-app')->assertOk();
        $this->pushServiceAnswers(new Response(201), new Response(410));

        $this->addToCart(Product::factory()->create(['price' => 45]))->assertOk();
        $this->placeOrder(['billing_first_name' => 'Karim Haddad', 'billing_city' => 'Achrafieh'])->assertOk();

        $this->assertCount(2, $this->sent);
        $request = $this->sent[0]['request'];
        $this->assertSame('https://web.push.apple.com/iphone', (string) $request->getUri());
        $this->assertStringStartsWith('vapid t=', $request->getHeaderLine('Authorization'));
        $this->assertSame('high', $request->getHeaderLine('Urgency'));
        $this->assertSame('aes128gcm', $request->getHeaderLine('Content-Encoding'));

        $this->assertSame(['https://web.push.apple.com/iphone'], PushSubscription::pluck('endpoint')->all(), 'the ended one is forgotten');
    }

    public function test_the_notification_names_the_order_and_pickups(): void
    {
        $order = Order::factory()->create(['total_amount' => 45]);
        ShippingDetail::factory()->create(['order_id' => $order->id, 'name' => 'Karim Haddad', 'city' => 'Achrafieh']);

        $this->assertSame([
            'title' => "New order #{$order->id} · \$45.00",
            'body' => 'Karim Haddad · Achrafieh',
            'url' => route('admin.orders.details', $order->id),
            'tag' => "order-{$order->id}",
        ], AdminPush::orderMessage($order));

        $order->update(['delivery_method' => Order::PICKUP]);
        $this->assertSame('Karim Haddad · Store pickup', AdminPush::orderMessage($order->fresh())['body']);
    }

    public function test_the_test_button_and_removing_a_device(): void
    {
        $this->admin();
        $this->postJson('/admin/push/test')->assertStatus(422)->assertJsonPath('message', 'No device has notifications turned on yet.');

        $this->subscribe('https://web.push.apple.com/iphone')->assertOk();
        $this->pushServiceAnswers(new Response(201));
        $this->postJson('/admin/push/test')->assertOk()->assertJsonPath('message', 'Test notification sent to 1 device.');
        $this->assertCount(1, $this->sent);

        $this->delete('/admin/push/devices/' . PushSubscription::sole()->id)->assertRedirect('/admin/settings');
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_orders_place_fine_with_no_device_and_with_a_push_service_down(): void
    {
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder()->assertOk();    // no device: nothing is sent

        $this->admin();
        $this->subscribe('https://web.push.apple.com/iphone')->assertOk();
        $this->pushServiceAnswers(new Response(500));
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder()->assertOk()->assertJsonPath('success', true);
        $this->assertSame(2, Order::count());
        $this->assertSame(1, PushSubscription::count(), 'a failed send keeps the device');
    }
}
