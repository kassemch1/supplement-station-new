<?php

namespace App\Http\Controllers\user_controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    /**
     * Handle subscription form submission
     */
    public function subscribe(Request $request)
    {
        // Validate the email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid email address.'
            ], 422);
        }

        $email = $request->input('email');

        try {
            // Check if email already exists in database (regardless of status)
            $existingSubscription = Subscription::where('email', $email)->first();

            if ($existingSubscription) {
                // Email already exists in database - tell user it's already subscribed
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already subscribed. Please try a different email address.'
                ]);
            }

            // Create new subscription
            Subscription::create([
                'email' => $email,
                'is_active' => true,
                'subscribed_at' => now()
            ]);

            // Mark session as subscription processed
            session(['subscription_processed' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! You will receive our latest updates and offers.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Subscription error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your subscription. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle subscription cancellation (user clicked "No, Thanks")
     */
    public function cancel(Request $request)
    {
        // Mark session as subscription processed to prevent modal from showing again
        session(['subscription_processed' => true]);

        return response()->json([
            'success' => true,
            'message' => 'No problem! You can always subscribe later.'
        ]);
    }

    /**
     * Check if subscription modal should be shown
     */
    public function shouldShowModal()
    {
        return response()->json([
            'show_modal' => !session('subscription_processed', false)
        ]);
    }
}
