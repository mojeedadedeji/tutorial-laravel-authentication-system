<?php

namespace Tests\Feature;

use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_shipment_records_initial_status(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $this->assertDatabaseHas('shipment_status_histories', [
            'shipment_id' => $shipment->id,
            'status' => 'pending',
        ]);
    }

    public function test_status_change_appends_history_entry(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $shipment->update(['status' => 'in_transit']);
        $shipment->update(['status' => 'delivered', 'delivered_at' => now()]);

        $this->assertSame(
            ['pending', 'in_transit', 'delivered'],
            $shipment->statusHistories()->pluck('status')->all()
        );
    }

    public function test_non_status_update_does_not_add_history(): void
    {
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $shipment->update(['weight_kg' => 50]);

        $this->assertCount(1, $shipment->statusHistories);
    }

    public function test_tracking_page_shows_history(): void
    {
        $shipment = Shipment::factory()->create([
            'reference' => 'SHP-HIST01',
            'status' => 'pending',
        ]);
        $shipment->update(['status' => 'in_transit']);
        $shipment->update(['status' => 'delivered', 'delivered_at' => now()]);

        $this->get('/track/SHP-HIST01')
            ->assertOk()
            ->assertSee('Shipment created')
            ->assertSee('In transit')
            ->assertSee('Delivered');
    }
}
