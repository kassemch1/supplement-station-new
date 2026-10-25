<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Emails one product to every active subscriber. The live server has no queue worker, so the
// controller runs this right after the response has been sent (dispatchAfterResponse); with a
// worker it can be queued as it is.
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
        $failed = 0;

        Subscription::active()->each(function (Subscription $subscriber) use ($product, &$sent, &$failed) {
            $unsubscribeUrl = $subscriber->unsubscribeUrl();
            try {
                Mail::send('mails.product-newsletter', compact('product', 'subscriber', 'unsubscribeUrl'),
                    function (Message $message) use ($subscriber, $product, $unsubscribeUrl) {
                        $message->to($subscriber->email)
                            ->subject('New Product: ' . $product->name)
                            ->from(config('mail.from.address'), config('mail.from.name'))
                            ->replyTo(config('mail.admin_address'), 'Supplement Station');
                        // Lets Gmail, Outlook and Apple Mail show their own one-click "Unsubscribe"
                        $headers = $message->getSymfonyMessage()->getHeaders();
                        $headers->addTextHeader('List-Unsubscribe', "<{$unsubscribeUrl}>");
                        $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                    });
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Failed to send email to ' . $subscriber->email . ': ' . $e->getMessage());
            }
        });

        Log::info("Product email campaign sent for product ID {$this->productId}. Sent: {$sent}, Failed: {$failed}");
    }
}
