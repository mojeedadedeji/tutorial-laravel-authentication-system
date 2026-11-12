<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment received — '.$this->order->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('A buyer has paid ₦'.number_format($this->order->total).' for "'.$this->order->title.'" via Paystack.')
            ->action('View order', config('app.frontend_url', 'http://localhost:8123').'#/orders')
            ->line('Please dispatch the item once you are ready.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_received',
            'order_id' => $this->order->id,
            'title' => $this->order->title,
            'amount' => $this->order->total,
            'message' => 'Payment of ₦'.number_format($this->order->total).' received for "'.$this->order->title.'".',
        ];
    }
}
