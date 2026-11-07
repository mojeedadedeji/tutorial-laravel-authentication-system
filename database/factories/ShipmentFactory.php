<?php

namespace Database\Factories;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cities = ['Lagos', 'Abuja', 'Port Harcourt', 'Kano', 'Ibadan', 'Enugu', 'Benin City', 'Kaduna', 'Ilorin', 'Uyo'];
        $origin = fake()->randomElement($cities);
        $destination = fake()->randomElement(array_diff($cities, [$origin]));

        $status = fake()->randomElement(['pending', 'in_transit', 'delivered', 'delivered', 'delivered', 'delayed', 'cancelled']);
        $shippedAt = fake()->dateTimeBetween('-12 months', 'now');
        $delivered = in_array($status, ['delivered', 'delayed'], true)
            ? (clone $shippedAt)->modify('+' . fake()->numberBetween(1, 5) . ' days')
            : null;

        return [
            'user_id' => \App\Models\User::factory(),
            'reference' => 'SHP-' . fake()->unique()->numerify('######'),
            'origin' => $origin,
            'destination' => $destination,
            'status' => $status,
            'weight_kg' => fake()->randomFloat(2, 50, 25000),
            'amount' => fake()->randomFloat(2, 15000, 850000),
            'shipped_at' => $shippedAt,
            'delivered_at' => $delivered,
        ];
    }
}
