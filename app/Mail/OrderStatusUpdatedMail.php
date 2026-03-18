<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<string, string>
     */
    private const STATUS_MESSAGES = [
        'paid' => 'Payment confirmed! We\'re preparing your order.',
        'shipped' => 'Your order is on its way!',
        'delivered' => 'Your order has been delivered.',
        'cancelled' => 'Your order has been cancelled.',
    ];

    public function __construct(
        public Order $order,
        public string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Your Order TM-%06d is now %s', $this->order->id, ucfirst($this->newStatus)),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-status-updated',
            with: [
                'order' => $this->order,
                'orderNumber' => sprintf('TM-%06d', $this->order->id),
                'newStatus' => $this->newStatus,
                'statusMessage' => self::STATUS_MESSAGES[$this->newStatus] ?? 'Your order status has been updated.',
                'orderUrl' => url('/account/orders/'.$this->order->id),
            ],
        );
    }
}
