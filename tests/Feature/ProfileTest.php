<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
        $this->put('/profile', [])->assertRedirect(route('login'));
    }

    public function test_profile_page_shows_user_information(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_user_can_update_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '+234 800 000 0000',
            'address' => '12 Marina Road, Lagos, Nigeria',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '+234 800 000 0000',
            'address' => '12 Marina Road, Lagos, Nigeria',
        ]);
    }

    public function test_email_must_be_unique_across_other_users(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_phone_and_address_are_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->phone);
        $this->assertNull($user->fresh()->address);
    }

    public function test_user_can_delete_their_account_with_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_account_deletion_requires_correct_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_guests_cannot_delete_accounts(): void
    {
        $this->delete('/profile', ['password' => 'password'])
            ->assertRedirect(route('login'));
    }

    public function test_user_can_keep_their_own_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors();
    }
}
