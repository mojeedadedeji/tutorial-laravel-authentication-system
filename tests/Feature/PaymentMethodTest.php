<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_payment_methods(): void
    {
        $this->get('/payment-methods')->assertRedirect(route('login'));
    }

    public function test_user_can_view_payment_methods_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/payment-methods');

        $response->assertStatus(200);
        $response->assertSee('Payment methods');
    }

    public function test_user_can_add_a_payment_method(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payment-methods', [
            'brand' => 'visa',
            'cardholder_name' => 'Test User',
            'card_number' => '4242424242424242',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('payment_methods', [
            'user_id' => $user->id,
            'brand' => 'visa',
            'last4' => '4242',
            'is_default' => true,
        ]);
    }

    public function test_only_last_four_digits_are_stored(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/payment-methods', [
            'brand' => 'mastercard',
            'cardholder_name' => 'Test User',
            'card_number' => '5555555555554444',
            'expiry_month' => 6,
            'expiry_year' => (int) date('Y') + 1,
        ]);

        $method = $user->paymentMethods()->first();
        $this->assertSame('4444', $method->last4);
        $this->assertStringNotContainsString('5555555555554444', json_encode($method->getAttributes()));
    }

    public function test_card_number_is_validated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payment-methods', [
            'brand' => 'visa',
            'cardholder_name' => 'Test User',
            'card_number' => '123',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 1,
        ]);

        $response->assertSessionHasErrors('card_number');
    }

    public function test_user_can_remove_their_payment_method(): void
    {
        $user = User::factory()->create();
        $method = PaymentMethod::create([
            'user_id' => $user->id,
            'brand' => 'visa',
            'last4' => '4242',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
            'cardholder_name' => 'Test User',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->delete("/payment-methods/{$method->id}");

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('payment_methods', ['id' => $method->id]);
    }

    public function test_user_cannot_remove_another_users_payment_method(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $method = PaymentMethod::create([
            'user_id' => $other->id,
            'brand' => 'visa',
            'last4' => '4242',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
            'cardholder_name' => 'Other User',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->delete("/payment-methods/{$method->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('payment_methods', ['id' => $method->id]);
    }
}
