<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $status,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $label = str_replace('_', ' ', $this->status);

        return (new MailMessage)
            ->subject('Declutter NG — order update')
            ->line("Your order for \"{$this->order->title}\" is now: {$label}.")
            ->action('View order', config('app.frontend_url', 'http://localhost:8123').'#/orders');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order',
            'order_id' => $this->order->id,
            'status' => $this->status,
            'title' => $this->order->title,
        ];
    }
}
