<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $apiKey;

    private string $senderId;

    public function __construct()
    {
        $this->apiKey = (string) config('services.termii.api_key');
        $this->senderId = (string) config('services.termii.sender_id', 'N-Alert');
    }

    public function configured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Send an SMS. Uses Termii when configured, otherwise logs it.
     */
    public function send(string $phone, string $message): void
    {
        $phone = $this->normalize($phone);

        if (! $this->configured()) {
            Log::info("SMS (not configured) to {$phone}: {$message}");

            return;
        }

        $response = Http::asForm()->post('https://v3.api.termii.com/api/sms/send', [
            'api_key' => $this->apiKey,
            'to' => $phone,
            'from' => $this->senderId,
            'sms' => $message,
            'type' => 'plain',
            'channel' => 'generic',
        ])->throw()->json();

        if (($response['code'] ?? null) !== 'ok') {
            throw new \RuntimeException($response['message'] ?? 'SMS delivery failed.');
        }
    }

    /**
     * Normalize to international format digits only (e.g. 2348012345678).
     */
    public function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '234')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '234' . substr($digits, 1);
        }

        return $digits;
    }
}
