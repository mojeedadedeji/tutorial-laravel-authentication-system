<?php

namespace App\Notifications;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipmentStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public Shipment $shipment) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Shipment {$this->shipment->reference} status: " . ucfirst(str_replace('_', ' ', $this->shipment->status)))
            ->greeting("Hello {$notifiable->name},")
            ->line("Your shipment **{$this->shipment->reference}** ({$this->shipment->origin} → {$this->shipment->destination}) has been updated.")
            ->line("**New status:** " . ucfirst(str_replace('_', ' ', $this->shipment->status)))
            ->action('Track shipment', route('shipments.show', $this->shipment))
            ->line('Thank you for choosing 5050 Logistics Limited.');
    }
}
