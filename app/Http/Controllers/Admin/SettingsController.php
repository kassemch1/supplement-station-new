<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewOrderMail;
use App\Models\PushSubscription;
use App\Models\Setting;
use App\Services\AdminPush;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    const MAX_ORDER_EMAILS = 5;

    public function edit(AdminPush $push)
    {
        return view('admin_views.settings', [
            'orderEmails' => implode(', ', NewOrderMail::recipients()),
            'maxOrderEmails' => self::MAX_ORDER_EMAILS,
            'pushKey' => $push->publicKey(),
            'pushDevices' => PushSubscription::orderBy('created_at')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate(['order_emails' => 'nullable|string|max:1000']);

        $emails = NewOrderMail::addressList((string) $request->order_emails);
        if ($emails === []) {
            throw ValidationException::withMessages(['order_emails' => 'Enter at least one email address.']);
        }
        if (count($emails) > self::MAX_ORDER_EMAILS) {
            throw ValidationException::withMessages(['order_emails' => 'Enter at most ' . self::MAX_ORDER_EMAILS . ' email addresses.']);
        }
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['order_emails' => "\"$email\" is not a valid email address."]);
            }
        }

        Setting::write(NewOrderMail::RECIPIENTS_SETTING, implode(', ', $emails));

        return redirect()->route('admin.settings')
            ->with('success', 'Saved. New orders will be emailed to ' . implode(', ', $emails) . '.');
    }

    // Sent at once (not after the response), so the admin sees whether the mail server took it
    public function testEmail()
    {
        $to = NewOrderMail::recipients();

        try {
            Mail::raw("This is a test from the Supplement Station admin.\n\nNew order emails will arrive at this address.",
                fn ($message) => $message->to($to)
                    ->from(config('mail.admin_address'), 'Supplement Station')
                    ->subject('Test: new order emails'));
        } catch (\Throwable $e) {
            Log::error('Test order email failed', ['error' => $e->getMessage()]);

            return redirect()->route('admin.settings')->with('error', 'The test email could not be sent: ' . $e->getMessage());
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Test email sent to ' . implode(', ', $to) . '. If it is not in the inbox, check the spam folder.');
    }
}
