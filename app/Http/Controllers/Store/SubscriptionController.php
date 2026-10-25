<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// The newsletter pop-up, and the unsubscribe link at the bottom of every newsletter
class SubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validator = validator($request->all(), ['email' => 'required|email|max:255']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
        }

        try {
            $subscription = Subscription::firstOrNew(['email' => $request->input('email')]);

            if ($subscription->exists && $subscription->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already subscribed. Please try a different email address.',
                ]);
            }

            // New, or coming back after unsubscribing
            $subscription->fill(['is_active' => true, 'subscribed_at' => now()])->save();
            session(['subscription_processed' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! You will receive our latest updates and offers.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Subscription error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your subscription. Please try again.',
            ], 500);
        }
    }

    // "No, thanks" in the pop-up: don't ask again this visit
    public function cancel(): JsonResponse
    {
        session(['subscription_processed' => true]);

        return response()->json(['success' => true, 'message' => 'No problem! You can always subscribe later.']);
    }

    public function shouldShowModal(): JsonResponse
    {
        return response()->json(['show_modal' => !session('subscription_processed', false)]);
    }

    // The link in the email opens a page with a button. Email apps that offer their own
    // "Unsubscribe" button (List-Unsubscribe header) POST to the same signed address.
    public function unsubscribePage(Subscription $subscription)
    {
        return view('user_views.unsubscribe', ['subscription' => $subscription, 'done' => !$subscription->is_active]);
    }

    public function unsubscribe(Request $request, Subscription $subscription)
    {
        $subscription->update(['is_active' => false]);
        Log::info('Newsletter unsubscribe', ['subscription' => $subscription->id]);

        // An email app's one-click request (body "List-Unsubscribe=One-Click") needs no page
        if ($request->has('List-Unsubscribe')) {
            return response()->noContent();
        }

        return view('user_views.unsubscribe', ['subscription' => $subscription, 'done' => true]);
    }
}
