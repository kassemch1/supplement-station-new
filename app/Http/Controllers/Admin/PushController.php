<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\AdminPush;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Turning new-order notifications on and off for a device, from Admin > Settings (see App\Services\AdminPush)
class PushController extends Controller
{
    // The key a device subscribes with, asked for only when the admin turns notifications on
    public function key(AdminPush $push): JsonResponse
    {
        $key = $push->publicKey();

        return $key
            ? response()->json(['key' => $key])
            : response()->json(['message' => 'Notifications can\'t be set up on this server (PHP\'s OpenSSL could not make the keys).'], 503);
    }

    // What the browser gives once the admin allowed notifications (PushSubscription.toJSON())
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|url:https|max:2000',
            'keys.p256dh' => 'required|string|max:255',
            'keys.auth' => 'required|string|max:255',
        ]);

        PushSubscription::updateOrCreate(['endpoint_hash' => PushSubscription::hashOf($data['endpoint'])], [
            'user_id' => $request->user()->id,
            'endpoint' => $data['endpoint'],
            'public_key' => $data['keys']['p256dh'],
            'auth_token' => $data['keys']['auth'],
            'device' => PushSubscription::deviceName($request->userAgent()),
        ]);

        return response()->json(['success' => true, 'message' => 'Notifications are on for this device.']);
    }

    // "Turn off on this device"
    public function unsubscribe(Request $request): JsonResponse
    {
        $endpoint = (string) $request->validate(['endpoint' => 'required|string|max:2000'])['endpoint'];
        PushSubscription::where('endpoint_hash', PushSubscription::hashOf($endpoint))->delete();

        return response()->json(['success' => true, 'message' => 'Notifications are off for this device.']);
    }

    // "Remove" next to a device in the list (one that is lost, or not at hand to turn it off)
    public function destroy(int $id)
    {
        PushSubscription::whereKey($id)->delete();

        return redirect()->route('admin.settings')->with('success', 'That device will no longer get notifications.');
    }

    public function test(AdminPush $push): JsonResponse
    {
        try {
            $sent = $push->test();
        } catch (\Throwable $e) {
            Log::error('Test notification failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'The test notification could not be sent: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => $sent > 0,
            'message' => $sent ? "Test notification sent to $sent " . ($sent === 1 ? 'device.' : 'devices.') : 'No device has notifications turned on yet.',
        ], $sent ? 200 : 422);
    }
}
