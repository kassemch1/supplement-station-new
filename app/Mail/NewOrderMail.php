<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// Tells the store admin a new order came in. Replying writes to the customer when they gave an email.
class NewOrderMail extends Mailable
{
    // The addresses it goes to, set in Admin > Settings
    public const RECIPIENTS_SETTING = 'order_emails';

    public function __construct(public Order $order)
    {
    }

    // The addresses saved in Admin > Settings, or the store's mailbox until any are saved
    public static function recipients(): array
    {
        $saved = self::addressList((string) Setting::read(self::RECIPIENTS_SETTING));

        return $saved ?: [config('mail.admin_address')];
    }

    // "a@x.com, b@y.com" (commas, semicolons, spaces or new lines between them) as a list, each once
    public static function addressList(string $text): array
    {
        $addresses = preg_split('/[\s,;]+/', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique($addresses));
    }

    public function envelope(): Envelope
    {
        $customer = $this->order->shippingDetail;

        return new Envelope(
            from: new Address(config('mail.admin_address'), 'Supplement Station'),
            replyTo: $customer?->email ? [new Address($customer->email, $customer->name)] : [],
            subject: "New order #{$this->order->id} - {$this->order->formatted_total}" . ($this->order->isPickup() ? ' - Store pickup' : ''),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mails.new_order_admin');
    }
}
