<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailAddress extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

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
        $url = rtrim(config('app.frontend_url', 'http://localhost:8123'), '/')
            .'#/verify-email?token='.$this->token;

        return (new MailMessage)
            ->subject('Verify your Declutter NG email')
            ->line('Click the button below to verify your email address.')
            ->action('Verify email', $url)
            ->line('This link expires in 60 minutes. If you did not request this, ignore this email.');
    }
}
