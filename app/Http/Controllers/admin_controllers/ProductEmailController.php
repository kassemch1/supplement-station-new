<?php

namespace App\Http\Controllers\admin_controllers;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
class ProductEmailController extends Controller
{
    /**
     * Send product email to all active subscribers
     */
    public function sendToSubscribers(Request $request)
    {
        try {
            $productId = $request->input('product_id');

            // Get the product with its images
            $product = Product::with(['images', 'getCategory'])->findOrFail($productId);

            // Get all active subscribers
            $activeSubscribers = Subscription::where('is_active', true)->get();

            if ($activeSubscribers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscribers found.'
                ]);
            }

            $sentCount = 0;
            $failedEmails = [];

            foreach ($activeSubscribers as $subscriber) {
                try {
                    Mail::send('mails.product-newsletter', [
                        'product' => $product,
                        'subscriber' => $subscriber
                    ], function ($message) use ($subscriber, $product) {
                        $message->to($subscriber->email)
                            ->subject('New Product: ' . $product->name)
                            ->from(config('mail.from.address'), config('mail.from.name'));
                    });

                    $sentCount++;
                } catch (\Exception $e) {
                    $failedEmails[] = $subscriber->email;
                    Log::error('Failed to send email to ' . $subscriber->email . ': ' . $e->getMessage());
                }
            }

            // Log the email campaign
            Log::info("Product email campaign sent for product ID {$productId}. Sent: {$sentCount}, Failed: " . count($failedEmails));

            $message = "Email sent successfully to {$sentCount} subscriber(s).";
            if (!empty($failedEmails)) {
                $message .= " Failed to send to " . count($failedEmails) . " email(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'sent_count' => $sentCount,
                'failed_count' => count($failedEmails)
            ]);

        } catch (\Exception $e) {
            Log::error('Product email campaign failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send emails. Please try again.'
            ], 500);
        }
    }

    /**
     * Get preview of the product email
     */
    public function previewEmail($productId)
    {
        $product = Product::with(['images', 'getCategory'])->findOrFail($productId);

        // Create a dummy subscriber for preview
        $subscriber = (object) [
            'email' => 'preview@example.com',
            'subscribed_at' => now()
        ];

        return view('mails.product-newsletter', [
            'product' => $product,
            'subscriber' => $subscriber
        ]);
    }
}
