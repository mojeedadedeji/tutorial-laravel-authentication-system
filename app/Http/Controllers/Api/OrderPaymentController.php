<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\PaymentReceived;
use App\Services\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderPaymentController extends Controller
{
    public function __construct(private PaystackService $paystack) {}

    /**
     * Initialize a Paystack payment for an unpaid order.
     * Returns the Paystack authorization URL the client redirects to.
     */
    public function initialize(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        abort_unless($order->buyer_id === $user->id, 403);
        abort_if($order->status === 'cancelled', 422, 'This order was cancelled.');
        abort_if($order->payment_status === 'paid', 422, 'This order is already paid.');
        abort_unless($this->paystack->configured(), 503, 'Online payments are not configured yet.');

        $reference = 'DN-'.$order->id.'-'.strtoupper(Str::random(8));

        $data = $this->paystack->initialize(
            $order->total * 100, // kobo
            $user->email,
            $reference,
            $this->callbackUrl($order),
        );

        $order->update([
            'payment_method' => 'paystack',
            'payment_status' => 'unpaid',
            'paystack_reference' => $reference,
        ]);

        return response()->json([
            'authorization_url' => $data['authorization_url'],
            'reference' => $reference,
        ]);
    }

    /**
     * Verify a Paystack payment after the buyer returns from checkout.
     */
    public function verify(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();
        abort_unless($order->buyer_id === $user->id, 403);

        if ($order->payment_status === 'paid') {
            return response()->json(['paid' => true, 'order' => $this->orderPayload($order)]);
        }

        abort_unless($order->paystack_reference === $data['reference'], 422, 'Reference mismatch.');

        if (! $this->paystack->verify($data['reference'])) {
            return response()->json(['paid' => false, 'message' => 'Payment not confirmed yet.'], 402);
        }

        $this->markPaid($order);

        return response()->json(['paid' => true, 'order' => $this->orderPayload($order->fresh())]);
    }

    /**
     * Paystack webhook — charge.success marks the order paid even if the
     * buyer never returns to the callback URL.
     */
    public function webhook(Request $request): JsonResponse
    {
        $secret = (string) config('services.paystack.secret');
        $signature = (string) $request->header('x-paystack-signature', '');

        if ($secret === '' || ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload = $request->all();

        if (($payload['event'] ?? null) !== 'charge.success') {
            return response()->json(['received' => true]);
        }

        $reference = $payload['data']['reference'] ?? null;
        $order = $reference ? Order::where('paystack_reference', $reference)->first() : null;

        if ($order && $order->payment_status !== 'paid') {
            $this->markPaid($order);
        }

        return response()->json(['received' => true]);
    }

    private function markPaid(Order $order): void
    {
        $order->payment_status = 'paid';
        $order->paid_at = now();
        $order->save();

        $history = $order->status_history ?? [];
        $history[] = [
            'status' => $order->status,
            'time' => now()->toIso8601String(),
            'note' => 'Payment received via Paystack ('.($order->paystack_reference ?? '').')',
        ];
        $order->status_history = $history;
        $order->save();

        $order->seller?->notify(new PaymentReceived($order));
    }

    private function callbackUrl(Order $order): string
    {
        $base = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return $base.'/?paystack=callback&order=api-'.$order->id;
    }

    private function orderPayload(Order $order): array
    {
        return [
            'id' => 'api-'.$order->id,
            'payment_status' => $order->payment_status,
            'paystack_reference' => $order->paystack_reference,
            'paid_at' => $order->paid_at?->toIso8601String(),
        ];
    }
}
