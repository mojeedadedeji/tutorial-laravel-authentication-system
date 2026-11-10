<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SavedSearchMatch extends Notification
{
    use Queueable;

    public function __construct(
        public Listing $listing,
        public string $searchName,
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
        return (new MailMessage)
            ->subject('Declutter NG — new match for "'.$this->searchName.'"')
            ->line("A new listing matches your saved search \"{$this->searchName}\": {$this->listing->title} — ₦".number_format($this->listing->price))
            ->action('View listing', config('app.frontend_url', 'http://localhost:8123').'#/item/api-'.$this->listing->id);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'saved_search',
            'listing_id' => $this->listing->id,
            'title' => $this->listing->title,
            'price' => $this->listing->price,
            'search' => $this->searchName,
        ];
    }
}
