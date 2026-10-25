<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

// The customer's receipt. Replying reaches the store.
class CheckoutMail extends Mailable
{
    public function __construct(public array $client_data, public Collection $items, public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.admin_address'), 'Supplement Station'),
            replyTo: [new Address(config('mail.admin_address'), 'Supplement Station')],
            subject: 'Checkout From Supplement-Station',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mails/checkout_mail');
    }
}
