<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendProductNewsletter;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductEmailController extends Controller
{
    // Emails the product to every active subscriber, after the admin has the answer
    public function sendToSubscribers(Request $request): JsonResponse
    {
        $product = Product::findOrFail($request->input('product_id'));
        $subscribers = Subscription::active()->count();

        if ($subscribers === 0) {
            return response()->json(['success' => false, 'message' => 'No active subscribers found.']);
        }

        SendProductNewsletter::dispatchAfterResponse($product->id);

        return response()->json([
            'success' => true,
            'message' => "Sending \"{$product->name}\" to {$subscribers} subscriber(s) now. You can keep working; failures are written to the log.",
        ]);
    }

    public function previewEmail(int $productId)
    {
        return view('mails.product-newsletter', [
            'product' => Product::with(['images', 'getCategory'])->findOrFail($productId),
            'subscriber' => (object) ['email' => 'preview@example.com', 'subscribed_at' => now()],
            'unsubscribeUrl' => '#',
        ]);
    }
}
