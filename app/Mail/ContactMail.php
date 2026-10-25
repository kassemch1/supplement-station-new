<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// A message from the contact form, sent to the store. Replying answers the visitor, not the store.
class ContactMail extends Mailable
{
    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->data['email'], $this->data['name'])],
            subject: 'Contact form: ' . $this->data['name'],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mails/contact_mail', with: ['data' => $this->data]);
    }
}
