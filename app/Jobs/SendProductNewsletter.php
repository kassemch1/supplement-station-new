<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Emails one product to every active subscriber. The admin page used to wait while every email was
// sent one by one. The live server has no queue worker, so the controller runs this right after the
// response has been sent (dispatchAfterResponse); with a worker it can be queued as it is.
class SendProductNewsletter implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $productId)
    {
    }

    public function handle(): void
    {
        // Runs after the admin got the answer: a long list must not be cut off by the PHP time limit
        ignore_user_abort(true);
        @set_time_limit(0);

        $product = Product::with(['images', 'getCategory'])->find($this->productId);
        if (!$product) {
            return;
        }

        $sent = 0;
        $failed = [];

        Subscription::where('is_active', true)->each(function ($subscriber) use ($product, &$sent, &$failed) {
            try {
                Mail::send('mails.product-newsletter', [
                    'product' => $product,
                    'subscriber' => $subscriber,
                ], function ($message) use ($subscriber, $product) {
                    $message->to($subscriber->email)
                        ->subject('New Product: ' . $product->name)
                        ->from(config('mail.from.address'), config('mail.from.name'));
                });
                $sent++;
            } catch (\Throwable $e) {
                $failed[] = $subscriber->email;
                Log::error('Failed to send email to ' . $subscriber->email . ': ' . $e->getMessage());
            }
        });

        Log::info("Product email campaign sent for product ID {$this->productId}. Sent: {$sent}, Failed: " . count($failed));
    }
}
