<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewReceived extends Notification
{
    use Queueable;

    public function __construct(public Review $review) {}

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
            ->subject('Declutter NG — you received a review')
            ->line("{$this->review->reviewer?->name} left you a {$this->review->rating}-star review.")
            ->action('View profile', config('app.frontend_url', 'http://localhost:8123').'#/profile');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review',
            'review_id' => $this->review->id,
            'rating' => $this->review->rating,
            'reviewer' => $this->review->reviewer?->name,
        ];
    }
}
