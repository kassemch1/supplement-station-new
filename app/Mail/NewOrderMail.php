<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// Tells the store admin a new order came in
class NewOrderMail extends Mailable
{
    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('admin@supplement-station.com', 'Supplement Station'),
            subject: "New order #{$this->order->id} - {$this->order->formatted_total}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mails.new_order_admin');
    }
}
