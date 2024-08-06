<?php

namespace App\Http\Controllers\user_controllers;

use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        Mail::to('kassemshames836@gmail.com')->send(new ContactMail($data));

        return back()->with('success', 'Thank you for contacting us!');
    }
}
