<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use App\Notifications\ShipmentStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ShipmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_shipment_detail(): void
    {
        $user = User::factory()->create();
        $shipment = Shipment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/shipments/{$shipment->id}");

        $response->assertStatus(200);
        $response->assertSee($shipment->reference);
        $response->assertSee('Status timeline');
    }

    public function test_users_cannot_view_others_shipments(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $shipment = Shipment::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)->get("/shipments/{$shipment->id}")->assertForbidden();
    }

    public function test_user_can_update_shipment_status_and_gets_notified(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $shipment = Shipment::factory()->create(['user_id' => $user->id, 'status' => 'in_transit']);

        $response = $this->actingAs($user)->patch("/shipments/{$shipment->id}/status", [
            'status' => 'delivered',
        ]);

        $response->assertRedirect();
        $this->assertSame('delivered', $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->delivered_at);
        Notification::assertSentTo($user, ShipmentStatusUpdated::class);
    }

    public function test_user_can_edit_a_shipment(): void
    {
        $user = User::factory()->create();
        $shipment = Shipment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/shipments/{$shipment->id}", [
            'origin' => 'Lagos',
            'destination' => 'Kano',
            'amount' => 99000,
            'status' => 'in_transit',
        ]);

        $response->assertRedirect(route('shipments.show', $shipment));
        $this->assertSame('Kano', $shipment->fresh()->destination);
        $this->assertSame('99000.00', $shipment->fresh()->amount);
    }

    public function test_user_can_delete_a_shipment(): void
    {
        $user = User::factory()->create();
        $shipment = Shipment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/shipments/{$shipment->id}");

        $response->assertRedirect(route('shipments.index'));
        $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
    }

    public function test_user_can_export_shipments_as_csv(): void
    {
        $user = User::factory()->create();
        Shipment::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/shipments/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Reference', $response->streamedContent());
    }

    public function test_user_can_view_shipment_invoice(): void
    {
        $user = User::factory()->create();
        $shipment = Shipment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/shipments/{$shipment->id}/invoice");

        $response->assertStatus(200);
        $response->assertSee('INVOICE');
        $response->assertSee($shipment->reference);
    }

    public function test_public_can_track_shipment_by_reference(): void
    {
        $shipment = Shipment::factory()->create(['reference' => 'SHP-TRACK1']);

        $response = $this->get('/track/SHP-TRACK1');

        $response->assertStatus(200);
        $response->assertSee('SHP-TRACK1');
        $response->assertSee($shipment->origin);
    }

    public function test_tracking_unknown_reference_returns_404(): void
    {
        $this->get('/track/SHP-NOPE')->assertNotFound();
    }

    public function test_quote_calculator_shows_estimate(): void
    {
        $response = $this->post('/quote', [
            'origin' => 'Lagos',
            'destination' => 'Abuja',
            'weight_kg' => 500,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Estimated total');
        $response->assertSee('Abuja');
    }

    public function test_quote_requires_valid_input(): void
    {
        $response = $this->post('/quote', [
            'origin' => 'Lagos',
            'destination' => 'Lagos',
            'weight_kg' => 0,
        ]);

        $response->assertSessionHasErrors(['destination', 'weight_kg']);
    }
}
