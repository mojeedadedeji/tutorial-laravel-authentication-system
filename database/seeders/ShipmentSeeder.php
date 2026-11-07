<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Seed realistic shipment data across the last 12 months.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        Shipment::factory()->count(180)->create(['user_id' => $user->id]);
    }
}
