<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Shipment;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private PaystackService $paystack) {}

    /**
     * Pay for a shipment. Uses Paystack when configured,
     * otherwise records a simulated payment.
     */
    public function store(Request $request, Shipment $shipment): RedirectResponse
    {
        abort_unless($shipment->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ]);

        $method = $request->user()->paymentMethods()->findOrFail($validated['payment_method_id']);
        $reference = 'PAY-' . strtoupper(Str::random(12));

        if ($this->paystack->configured()) {
            Payment::create([
                'user_id' => $request->user()->id,
                'shipment_id' => $shipment->id,
                'payment_method_id' => $method->id,
                'amount' => $shipment->amount,
                'method' => 'paystack',
                'status' => 'pending',
                'reference' => $reference,
            ]);

            $data = $this->paystack->initialize(
                $shipment->amount * 100,
                $request->user()->email,
                $reference,
                route('payments.callback'),
            );

            return redirect()->away($data['authorization_url']);
        }

        Payment::create([
            'user_id' => $request->user()->id,
            'shipment_id' => $shipment->id,
            'payment_method_id' => $method->id,
            'amount' => $shipment->amount,
            'method' => $method->type,
            'status' => 'paid',
            'reference' => $reference,
        ]);

        return back()->with('status', 'Payment successful. Thank you!');
    }

    /**
     * Paystack callback — verify the transaction server-side.
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        $payment = Payment::where('reference', $reference)->firstOrFail();
        abort_unless($payment->user_id === $request->user()->id, 403);

        if ($this->paystack->verify($reference)) {
            $payment->update(['status' => 'paid']);

            return redirect()->route('shipments.show', $payment->shipment)
                ->with('status', 'Payment successful. Thank you!');
        }

        $payment->update(['status' => 'failed']);

        return redirect()->route('shipments.show', $payment->shipment)
            ->with('status', 'Payment failed. Please try again.');
    }
}
