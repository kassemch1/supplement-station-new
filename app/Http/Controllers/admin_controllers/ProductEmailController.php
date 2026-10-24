<?php

namespace App\Http\Controllers\admin_controllers;
use App\Jobs\SendProductNewsletter;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\Request;
class ProductEmailController extends Controller
{
    /**
     * Send product email to all active subscribers (in the background, see SendProductNewsletter)
     */
    public function sendToSubscribers(Request $request)
    {
        $product = Product::findOrFail($request->input('product_id'));
        $subscribers = Subscription::where('is_active', true)->count();

        if ($subscribers === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscribers found.'
            ]);
        }

        SendProductNewsletter::dispatchAfterResponse($product->id);

        return response()->json([
            'success' => true,
            'message' => "Sending \"{$product->name}\" to {$subscribers} subscriber(s) now. You can keep working; failures are written to the log.",
        ]);
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
