<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Pay for a shipment using a saved payment method.
     * Simulated gateway — records a paid payment.
     */
    public function store(Request $request, Shipment $shipment): RedirectResponse
    {
        abort_unless($shipment->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ]);

        $method = $request->user()->paymentMethods()->findOrFail($validated['payment_method_id']);

        Payment::create([
            'user_id' => $request->user()->id,
            'shipment_id' => $shipment->id,
            'payment_method_id' => $method->id,
            'amount' => $shipment->amount,
            'method' => $method->type,
            'status' => 'paid',
            'reference' => 'PAY-' . strtoupper(Str::random(10)),
        ]);

        return back()->with('status', 'Payment successful. Thank you!');
    }
}
