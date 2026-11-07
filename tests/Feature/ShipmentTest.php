<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_shipments(): void
    {
        $this->get('/shipments')->assertRedirect(route('login'));
    }

    public function test_user_can_view_their_shipments(): void
    {
        $user = User::factory()->create();
        Shipment::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/shipments');

        $response->assertStatus(200);
        $response->assertSee('Shipments');
    }

    public function test_users_only_see_their_own_shipments(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Shipment::factory()->create([
            'user_id' => $other->id,
            'reference' => 'SHP-OTHER',
        ]);

        $response = $this->actingAs($user)->get('/shipments');

        $response->assertDontSee('SHP-OTHER');
    }

    public function test_shipments_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Shipment::factory()->create(['user_id' => $user->id, 'status' => 'delivered', 'reference' => 'SHP-DEL']);
        Shipment::factory()->create(['user_id' => $user->id, 'status' => 'in_transit', 'reference' => 'SHP-TRANSIT']);

        $response = $this->actingAs($user)->get('/shipments?status=in_transit');

        $response->assertSee('SHP-TRANSIT');
        $response->assertDontSee('SHP-DEL');
    }

    public function test_shipments_can_be_filtered_by_lane(): void
    {
        $user = User::factory()->create();
        Shipment::factory()->create([
            'user_id' => $user->id,
            'origin' => 'Lagos',
            'destination' => 'Abuja',
            'reference' => 'SHP-LAG-ABJ',
        ]);
        Shipment::factory()->create([
            'user_id' => $user->id,
            'origin' => 'Kano',
            'destination' => 'Ibadan',
            'reference' => 'SHP-KAN-IBD',
        ]);

        $response = $this->actingAs($user)->get('/shipments?origin=Lagos&destination=Abuja');

        $response->assertSee('SHP-LAG-ABJ');
        $response->assertDontSee('SHP-KAN-IBD');
    }

    public function test_shipments_can_be_searched_by_reference(): void
    {
        $user = User::factory()->create();
        Shipment::factory()->create(['user_id' => $user->id, 'reference' => 'SHP-ABC123']);
        Shipment::factory()->create(['user_id' => $user->id, 'reference' => 'SHP-XYZ999']);

        $response = $this->actingAs($user)->get('/shipments?q=ABC123');

        $response->assertSee('SHP-ABC123');
        $response->assertDontSee('SHP-XYZ999');
    }

    public function test_dashboard_shows_real_shipment_data(): void
    {
        $user = User::factory()->create();
        Shipment::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Shipments this month');
        $response->assertSee('Busiest lanes');
    }
}
