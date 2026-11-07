<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    private string $secretKey;

    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = (string) config('services.paystack.secret');
    }

    public function configured(): bool
    {
        return $this->secretKey !== '';
    }

    /**
     * Initialize a transaction. Returns the Paystack response data
     * (includes authorization_url and reference).
     */
    public function initialize(float $amountKobo, string $email, string $reference, string $callbackUrl): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $email,
                'amount' => (int) round($amountKobo),
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'currency' => 'NGN',
            ])
            ->throw()
            ->json();

        if (! ($response['status'] ?? false)) {
            throw new RuntimeException($response['message'] ?? 'Paystack initialization failed.');
        }

        return $response['data'];
    }

    /**
     * Verify a transaction by reference. Returns true when paid.
     */
    public function verify(string $reference): bool
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/" . urlencode($reference))
            ->throw()
            ->json();

        return ($response['status'] ?? false)
            && ($response['data']['status'] ?? null) === 'success';
    }
}
