<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FakeSms extends SmsService
{
    /** @var array<int, array{phone: string, message: string}> */
    public array $messages = [];

    public function send(string $phone, string $message): void
    {
        $this->messages[] = ['phone' => $this->normalize($phone), 'message' => $message];
    }

    public function lastCode(): string
    {
        preg_match('/\b(\d{6})\b/', end($this->messages)['message'], $m);

        return $m[1];
    }
}

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private FakeSms $sms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sms = new FakeSms;
        $this->instance(SmsService::class, $this->sms);
    }

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08012345678',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ], $overrides);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_registration_sends_otp_and_shows_verify_step(): void
    {
        $response = $this->post('/register', $this->registrationPayload());

        $response->assertRedirect(route('register.verify'));
        $this->assertGuest();
        $this->assertCount(1, $this->sms->messages);
        $this->assertSame('2348012345678', $this->sms->messages[0]['phone']);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_users_can_register_after_verifying_otp(): void
    {
        $this->post('/register', $this->registrationPayload());
        $code = $this->sms->lastCode();

        $response = $this->post('/register/verify', ['otp' => $code]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('2348012345678', $user->phone);
        $this->assertTrue(Hash::check('Password1', $user->password));
    }

    public function test_wrong_otp_is_rejected(): void
    {
        $this->post('/register', $this->registrationPayload());

        $response = $this->post('/register/verify', ['otp' => '000000']);

        $response->assertSessionHasErrors('otp');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_verify_page_redirects_without_pending_registration(): void
    {
        $this->get('/register/verify')->assertRedirect(route('register'));
    }

    public function test_resend_issues_new_otp(): void
    {
        $this->post('/register', $this->registrationPayload());

        $this->post('/register/resend')->assertRedirect();

        $this->assertCount(2, $this->sms->messages);
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', $this->registrationPayload(['email' => 'taken@example.com']));

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_requires_unique_phone(): void
    {
        User::factory()->create(['phone' => '2348012345678']);

        $response = $this->post('/register', $this->registrationPayload());

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->post('/register', $this->registrationPayload(['password_confirmation' => 'Different1']));

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_requires_strong_password(): void
    {
        $response = $this->post('/register', $this->registrationPayload(['password' => 'weak', 'password_confirmation' => 'weak']));

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_authenticated_users_are_redirected_away_from_register(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/register')->assertRedirect(route('dashboard'));
    }
}
