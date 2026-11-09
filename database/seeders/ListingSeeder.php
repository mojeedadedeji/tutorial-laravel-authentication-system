<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Seed marketplace sellers and sample listings for the API.
     */
    public function run(): void
    {
        $sellers = [
            ['name' => 'Adaeze Okafor', 'email' => 'adaeze@declutter.ng', 'location' => 'Lagos'],
            ['name' => 'Tunde Bakare', 'email' => 'tunde@declutter.ng', 'location' => 'Abuja'],
            ['name' => 'Funke Adeyemi', 'email' => 'funke@declutter.ng', 'location' => 'Ibadan'],
            ['name' => 'Ngozi Bello', 'email' => 'ngozi@declutter.ng', 'location' => 'Enugu'],
        ];

        $users = collect($sellers)->map(fn (array $s) => User::updateOrCreate(
            ['email' => $s['email']],
            ['name' => $s['name'], 'location' => $s['location'], 'password' => 'password']
        ));

        $items = [
            ['Ankara midi dress — size M', 18500, 'fashion', 'Like New', 'Lagos', 'Ankara print midi dress, worn twice. No stains.', 'Zara', 'M', 'both'],
            ['Men’s cotton linen shirt, XL', 6500, 'fashion', 'Good', 'Abuja', 'Lightweight linen shirt for harmattan.', 'H&M', 'XL', 'pickup'],
            ['Vintage denim jacket, size L', 12000, 'fashion', 'Good', 'Ibadan', 'Classic denim jacket, even fading, no rips.', 'Levi’s', 'L', 'pickup'],
            ['Nike Air Force 1 — UK 9', 38000, 'shoes', 'Like New', 'Lagos', 'Authentic AF1, original box included.', 'Nike', 'UK 9', 'both'],
            ['Leather tote bag — cognac', 22000, 'bags', 'Good', 'Lagos', 'Genuine leather tote with inner zip.', 'Coach', 'One size', 'pickup'],
            ['iPhone 12 — 128GB, battery 89%', 285000, 'electronics', 'Like New', 'Lagos', 'No scratches, battery health 89%, box + cable.', 'Apple', '128GB', 'both'],
            ['Samsung Galaxy A54 — 128GB', 165000, 'electronics', 'Good', 'Abuja', 'Screen protector always on, light frame scuffs.', 'Samsung', '128GB', 'pickup'],
            ['Sony WH-1000XM4 headphones', 95000, 'electronics', 'Like New', 'Port Harcourt', 'Flagship ANC headphones, new ear cushions.', 'Sony', 'WH-1000XM4', 'both'],
            ['HP laptop 15 — i5, 8GB RAM', 210000, 'electronics', 'Good', 'Ibadan', 'Windows 11, new SSD, charger included.', 'HP', '15.6" i5', 'both'],
            ['Smart TV 43" — Samsung, 4K', 145000, 'electronics', 'Good', 'Enugu', 'Wall mount included, works perfectly.', 'Samsung', '43" 4K', 'delivery'],
            ['Standing fan — 16 inch, remote', 8500, 'home', 'Good', 'Lagos', 'Oscillating fan with remote, cleaned blades.', 'Singer', '16"', 'pickup'],
            ['Microwave oven 25L', 18000, 'home', 'Like New', 'Abuja', 'Used 3 months, all functions work, box included.', 'Panasonic', '25L', 'both'],
            ['Washing machine 7kg top-load', 65000, 'home', 'Good', 'Ibadan', 'Fully automatic, serviced 2 months ago.', 'Hisense', '7kg', 'delivery'],
            ['Generator 2.5kVA — fairly used', 95000, 'home', 'Fair', 'Benin City', 'Starts easily, steady power, manual + key.', 'Elemax', '2.5kVA', 'both'],
        ];

        foreach ($items as $i => [$title, $price, $category, $condition, $location, $description, $brand, $size, $deliveryMode]) {
            Listing::updateOrCreate(
                ['title' => $title, 'user_id' => $users[$i % $users->count()]->id],
                [
                    'description' => $description,
                    'price' => $price,
                    'category' => $category,
                    'condition' => $condition,
                    'brand' => $brand,
                    'size' => $size,
                    'location' => $location,
                    'delivery_mode' => $deliveryMode,
                    'status' => 'active',
                    'photos' => [], // real uploads come later; front-end falls back to placeholders
                ]
            );
        }
    }
}
