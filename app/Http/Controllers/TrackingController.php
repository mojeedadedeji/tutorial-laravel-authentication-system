<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\View\View;

class TrackingController extends Controller
{
    /**
     * Public shipment tracking by reference.
     */
    public function show(string $reference): View
    {
        $shipment = Shipment::where('reference', strtoupper($reference))->firstOrFail();

        return view('tracking.show', [
            'shipment' => $shipment,
        ]);
    }
}
