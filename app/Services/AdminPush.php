<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PushSubscription;
use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Psr\Http\Client\ClientInterface;

// New-order notifications on the admin's phone or computer (Web Push). An iPhone gets them once the admin
// is on its home screen (iOS 16.4 or newer) and notifications were turned on there in Admin > Settings;
// they arrive with the phone locked and the app closed. The key pair that signs them is made on first use
// and kept in the settings table: changing it would stop every device's notifications until turned on again.
class AdminPush
{
    public const KEYS_SETTING = 'push_vapid_keys';

    // $client: tests pass one that answers without the network
    public function __construct(private ?ClientInterface $client = null)
    {
    }

    // The key a device needs to turn notifications on, or null when this server's OpenSSL can't make one
    // (PHP on Windows without OPENSSL_CONF, say)
    public function publicKey(): ?string
    {
        try {
            return $this->keys()['publicKey'];
        } catch (\Throwable $e) {
            Log::warning('Push notification keys could not be made', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function newOrder(Order $order): void
    {
        $this->send(self::orderMessage($order));
    }

    // What the notification says: "New order #612 · $45.00", "Karim Haddad · Achrafieh"; tapping it opens the order
    public static function orderMessage(Order $order): array
    {
        $order->loadMissing('shippingDetail');
        $where = $order->isPickup() ? 'Store pickup' : $order->shippingDetail?->city;

        return [
            'title' => "New order #{$order->id} · {$order->formatted_total}",
            'body' => implode(' · ', array_filter([$order->shippingDetail?->name, $where])),
            'url' => route('admin.orders.details', $order->id),
            'tag' => 'order-' . $order->id,
        ];
    }

    // How many devices took the test
    public function test(): int
    {
        return $this->send([
            'title' => 'Test notification',
            'body' => 'New orders will show up like this.',
            'url' => route('admin.settings'),
            'tag' => 'test',
        ]);
    }

    // Sends to every device that turned notifications on and returns how many took it. A device whose
    // notifications have ended (the app was deleted or notifications turned off) is forgotten.
    private function send(array $message): int
    {
        $subscriptions = PushSubscription::all();
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $keys = $this->keys();
        $push = new WebPush(
            ['VAPID' => ['subject' => 'mailto:' . config('mail.admin_address'), 'publicKey' => $keys['publicKey'], 'privateKey' => $keys['privateKey']]],
            ['TTL' => 86400, 'urgency' => 'high'],
            $this->client ?? new Client(['timeout' => 10]),
        );

        // the reports name the endpoint the way the HTTP client wrote it
        $ids = [];
        foreach ($subscriptions as $subscription) {
            $ids[(string) new Uri($subscription->endpoint)] = $subscription->id;
            $push->queueNotification(new Subscription($subscription->endpoint, $subscription->public_key, $subscription->auth_token, 'aes128gcm'),
                json_encode($message));
        }

        $sent = 0;
        foreach ($push->flush() as $report) {
            if ($report->isSuccess()) {
                $sent++;
            } elseif ($report->isSubscriptionExpired()) {
                PushSubscription::whereKey($ids[$report->getEndpoint()] ?? 0)->delete();
            } else {
                Log::warning('Push notification failed', ['reason' => $report->getReason()]);
            }
        }

        return $sent;
    }

    /** @return array{publicKey: string, privateKey: string} */
    private function keys(): array
    {
        $saved = Setting::read(self::KEYS_SETTING);
        if (!$saved) {
            // two requests making keys at the same moment keep the first pair saved
            Setting::query()->insertOrIgnore(['key' => self::KEYS_SETTING, 'value' => json_encode(VAPID::createVapidKeys()), 'created_at' => now(), 'updated_at' => now()]);
            $saved = Setting::read(self::KEYS_SETTING);
        }

        return json_decode($saved, true);
    }
}
