<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Your Tradition Me Order TM-%06d is Confirmed', $this->order->id),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-confirmation',
            with: [
                'order' => $this->order,
                'orderNumber' => sprintf('TM-%06d', $this->order->id),
                'orderUrl' => url('/account/orders/'.$this->order->id),
            ],
        );
    }
}
