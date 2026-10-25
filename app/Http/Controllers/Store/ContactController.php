<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function Illuminate\Support\defer;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        // Sent after the visitor has the answer; if the mail server fails, the message is kept in the log
        defer(function () use ($data) {
            try {
                Mail::to(config('mail.admin_address'))->send(new ContactMail($data));
            } catch (\Throwable $e) {
                Log::error('Contact form email failed', ['error' => $e->getMessage(), 'message' => $data]);
            }
        });

        return back()->with('success', 'Thank you for contacting us!');
    }
}
