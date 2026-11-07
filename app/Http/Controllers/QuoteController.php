<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    private const BASE_FARE = 15000.0;

    private const RATES_PER_KG = [
        'Lagos' => 350, 'Abuja' => 500, 'Port Harcourt' => 600,
        'Kano' => 800, 'Ibadan' => 400, 'Enugu' => 550,
        'Benin City' => 380, 'Kaduna' => 650, 'Ilorin' => 420, 'Uyo' => 520,
    ];

    private const DEFAULT_RATE = 450;

    /**
     * Show the quote calculator.
     */
    public function create(): View
    {
        return view('quote.create', [
            'cities' => array_keys(self::RATES_PER_KG),
        ]);
    }

    /**
     * Calculate a shipping quote.
     */
    public function calculate(Request $request): View
    {
        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:100'],
            'destination' => ['required', 'string', 'max:100', 'different:origin'],
            'weight_kg' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
        ]);

        $rate = self::RATES_PER_KG[$validated['destination']] ?? self::DEFAULT_RATE;
        $freight = $validated['weight_kg'] * $rate;
        $total = self::BASE_FARE + $freight;
        $etaDays = max(1, (int) ceil($validated['weight_kg'] / 2000) + 1);

        return view('quote.create', [
            'cities' => array_keys(self::RATES_PER_KG),
            'quote' => [
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'weight_kg' => $validated['weight_kg'],
                'rate_per_kg' => $rate,
                'base_fare' => self::BASE_FARE,
                'freight' => $freight,
                'total' => $total,
                'eta_days' => $etaDays,
            ],
        ]);
    }
}
