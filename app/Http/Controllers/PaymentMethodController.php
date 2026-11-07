<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    /**
     * Show the user's saved payment methods.
     */
    public function index(Request $request): View
    {
        $paymentMethods = $request->user()
            ->paymentMethods()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('payment-methods.index', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Store a new payment method for the user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', PaymentMethod::TYPES)],
            'brand' => ['nullable', 'string', 'max:20'],
            'cardholder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['nullable', 'required_if:type,card', 'string', 'regex:/^[0-9]{13,19}$/'],
            'expiry_month' => ['nullable', 'required_if:type,card', 'integer', 'min:1', 'max:12'],
            'expiry_year' => ['nullable', 'required_if:type,card', 'integer', 'min:' . date('Y'), 'max:' . (date('Y') + 20)],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isCard = $validated['type'] === 'card';

        DB::transaction(function () use ($request, $validated, $isCard) {
            $isDefault = $request->boolean('is_default')
                || ! $request->user()->paymentMethods()->exists();

            if ($isDefault) {
                $request->user()->paymentMethods()->update(['is_default' => false]);
            }

            $request->user()->paymentMethods()->create([
                'type' => $validated['type'],
                'brand' => $validated['brand'] ?? $validated['type'],
                'cardholder_name' => $validated['cardholder_name'],
                'last4' => $isCard ? substr($validated['card_number'], -4) : '----',
                'expiry_month' => $isCard ? $validated['expiry_month'] : null,
                'expiry_year' => $isCard ? $validated['expiry_year'] : null,
                'is_default' => $isDefault,
            ]);
        });

        return back()->with('status', 'Payment method added.');
    }

    /**
     * Remove a payment method belonging to the user.
     */
    public function destroy(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        abort_unless($paymentMethod->user_id === $request->user()->id, 403);

        $wasDefault = $paymentMethod->is_default;
        $paymentMethod->delete();

        if ($wasDefault) {
            $request->user()->paymentMethods()
                ->orderByDesc('created_at')
                ->first()
                ?->update(['is_default' => true]);
        }

        return back()->with('status', 'Payment method removed.');
    }
}
