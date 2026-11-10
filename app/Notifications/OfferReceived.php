<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferReceived extends Notification
{
    use Queueable;

    public function __construct(
        public Offer $offer,
        public string $event = 'received',
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
        $title = $this->offer->listing?->title ?? 'an item';
        $amount = '₦'.number_format($this->offer->counter_amount ?? $this->offer->amount);

        $line = match ($this->event) {
            'accepted' => "Your offer of {$amount} on \"{$title}\" was accepted — the price has been updated.",
            'declined' => "Your offer of {$amount} on \"{$title}\" was declined.",
            'countered' => "The seller countered your offer on \"{$title}\" with {$amount}.",
            default => "You received a new offer of {$amount} on \"{$title}\".",
        };

        return (new MailMessage)
            ->subject('Declutter NG — offer update')
            ->line($line)
            ->action('Open Declutter NG', config('app.frontend_url', 'http://localhost:8123'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer',
            'event' => $this->event,
            'offer_id' => $this->offer->id,
            'listing_id' => $this->offer->listing_id,
            'amount' => $this->offer->counter_amount ?? $this->offer->amount,
            'message' => $this->offer->listing?->title,
        ];
    }
}
