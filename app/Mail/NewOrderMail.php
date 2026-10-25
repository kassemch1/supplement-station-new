<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// Tells the store admin a new order came in. Replying writes to the customer when they gave an email.
class NewOrderMail extends Mailable
{
    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        $customer = $this->order->shippingDetail;

        return new Envelope(
            from: new Address(config('mail.admin_address'), 'Supplement Station'),
            replyTo: $customer?->email ? [new Address($customer->email, $customer->name)] : [],
            subject: "New order #{$this->order->id} - {$this->order->formatted_total}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mails.new_order_admin');
    }
}
