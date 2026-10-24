<?php

namespace App\Http\Controllers\user_controllers;

use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function Illuminate\Support\defer;

class ContactController
{
    public function sendEmailContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $data = $request->only('name', 'email', 'phone', 'message');

        // Sent after the visitor has the answer; if the mail server fails, the message is kept in the log
        defer(function () use ($data) {
            try {
                Mail::to('admin@supplement-station.com')->send(new ContactMail($data));
            } catch (\Throwable $e) {
                Log::error('Contact form email failed', ['error' => $e->getMessage(), 'message' => $data]);
            }
        });

        return back()->with('success', 'Thank you for contacting us!');
    }
}
