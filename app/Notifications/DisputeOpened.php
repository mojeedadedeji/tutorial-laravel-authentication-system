<?php

namespace App\Notifications;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeOpened extends Notification
{
    use Queueable;

    public function __construct(public Dispute $dispute) {}

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
        return (new MailMessage)
            ->subject('Declutter NG — dispute opened')
            ->line("A dispute was opened on order #{$this->dispute->order_id}: {$this->dispute->reason}")
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
            'type' => 'dispute',
            'dispute_id' => $this->dispute->id,
            'order_id' => $this->dispute->order_id,
            'reason' => \Illuminate\Support\Str::limit($this->dispute->reason, 120),
        ];
    }
}
