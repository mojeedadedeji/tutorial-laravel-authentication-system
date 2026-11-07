<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function userWithMethod(): array
    {
        $user = User::factory()->create();
        $method = PaymentMethod::create([
            'user_id' => $user->id,
            'type' => 'card',
            'brand' => 'visa',
            'last4' => '4242',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
            'cardholder_name' => 'Test',
        ]);
        $shipment = Shipment::factory()->create(['user_id' => $user->id, 'amount' => 25000]);

        return [$user, $method, $shipment];
    }

    public function test_pay_redirects_to_paystack_when_configured(): void
    {
        config(['services.paystack.secret' => 'sk_test_123']);
        [$user, $method, $shipment] = $this->userWithMethod();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/abc', 'reference' => 'PAY-X'],
            ]),
        ]);

        $response = $this->actingAs($user)->post("/shipments/{$shipment->id}/pay", [
            'payment_method_id' => $method->id,
        ]);

        $response->assertRedirect('https://checkout.paystack.com/abc');
        $this->assertDatabaseHas('payments', [
            'shipment_id' => $shipment->id,
            'status' => 'pending',
            'method' => 'paystack',
        ]);
    }

    public function test_callback_marks_payment_paid_on_success(): void
    {
        config(['services.paystack.secret' => 'sk_test_123']);
        [$user, $method, $shipment] = $this->userWithMethod();
        $payment = Payment::create([
            'user_id' => $user->id,
            'shipment_id' => $shipment->id,
            'payment_method_id' => $method->id,
            'amount' => 25000,
            'method' => 'paystack',
            'status' => 'pending',
            'reference' => 'PAY-TEST123',
        ]);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'success'],
            ]),
        ]);

        $this->actingAs($user)->get('/payments/callback?reference=PAY-TEST123')
            ->assertRedirect(route('shipments.show', $shipment));

        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_callback_marks_payment_failed_on_unsuccessful_verify(): void
    {
        config(['services.paystack.secret' => 'sk_test_123']);
        [$user, $method, $shipment] = $this->userWithMethod();
        $payment = Payment::create([
            'user_id' => $user->id,
            'shipment_id' => $shipment->id,
            'payment_method_id' => $method->id,
            'amount' => 25000,
            'method' => 'paystack',
            'status' => 'pending',
            'reference' => 'PAY-FAIL456',
        ]);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'failed'],
            ]),
        ]);

        $this->actingAs($user)->get('/payments/callback?reference=PAY-FAIL456');

        $this->assertSame('failed', $payment->fresh()->status);
    }

    public function test_simulated_payment_when_paystack_not_configured(): void
    {
        config(['services.paystack.secret' => null]);
        [$user, $method, $shipment] = $this->userWithMethod();

        $this->actingAs($user)->post("/shipments/{$shipment->id}/pay", [
            'payment_method_id' => $method->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'shipment_id' => $shipment->id,
            'status' => 'paid',
        ]);
    }
}
