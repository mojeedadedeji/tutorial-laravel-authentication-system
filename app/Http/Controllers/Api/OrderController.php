<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Create an order for an active listing. Marks the listing sold.
     * Payment is simulated — no real money moves.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'address' => ['nullable', 'string', 'max:300'],
            'payment_method' => ['nullable', Rule::in(['simulated_card', 'cash_pickup', 'simulated'])],
            'delivery_mode' => ['nullable', Rule::in(Listing::DELIVERY_MODES)],
        ]);

        $listing = Listing::with('user')->findOrFail($data['listing_id']);

        abort_if($listing->status !== 'active', 422, 'This item is no longer available.');
        abort_if($listing->user_id === $request->user()->id, 422, 'You cannot buy your own listing.');

        $deliveryCost = $this->deliveryCost($listing, $data['delivery_mode'] ?? null);
        $fee = Order::PROTO_FEE;

        $order = Order::create([
            'buyer_id' => $request->user()->id,
            'listing_id' => $listing->id,
            'seller_id' => $listing->user_id,
            'title' => $listing->title,
            'price' => $listing->price,
            'delivery_cost' => $deliveryCost,
            'fee' => $fee,
            'total' => $listing->price + $deliveryCost + $fee,
            'address' => $data['address'] ?? null,
            'payment_method' => $data['payment_method'] ?? 'simulated',
            'status' => 'awaiting_dispatch',
            'status_history' => [[
                'status' => 'awaiting_dispatch',
                'time' => now()->toIso8601String(),
                'note' => 'Order placed (simulated — no real payment)',
            ]],
        ]);

        $listing->update(['status' => 'sold']);

        return response()->json([
            'order' => $this->payload($order->fresh(['buyer', 'seller', 'listing'])),
        ], 201);
    }

    /**
     * The buyer's purchase history.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['buyer:id,name,location', 'seller:id,name,location', 'listing:id,title,photos'])
            ->latest()
            ->get()
            ->map(fn (Order $o) => $this->payload($o, 'buyer'));

        return response()->json(['orders' => $orders]);
    }

    /**
     * The seller's sales history.
     */
    public function sales(Request $request): JsonResponse
    {
        $orders = Order::where('seller_id', $request->user()->id)
            ->with(['buyer:id,name,location', 'seller:id,name,location', 'listing:id,title,photos'])
            ->latest()
            ->get()
            ->map(fn (Order $o) => $this->payload($o, 'seller'));

        return response()->json(['orders' => $orders]);
    }

    /**
     * Show one order — buyer or seller only.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        abort_unless($order->buyer_id === $user->id || $order->seller_id === $user->id, 403);

        $order->load(['buyer:id,name,location', 'seller:id,name,location', 'listing:id,title,photos']);

        $role = $order->buyer_id === $user->id && $order->seller_id === $user->id
            ? 'both'
            : ($order->buyer_id === $user->id ? 'buyer' : 'seller');

        return response()->json(['order' => $this->payload($order, $role)]);
    }

    /**
     * Transition an order's status. Buyer may cancel while awaiting
     * dispatch; seller drives dispatch/delivery; refunds after delivery.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $user = $request->user();
        $isBuyer = $order->buyer_id === $user->id;
        $isSeller = $order->seller_id === $user->id;
        abort_unless($isBuyer || $isSeller, 403);

        $target = $data['status'];

        // Role restrictions: only sellers dispatch/deliver; only buyers
        // cancel before dispatch; refunds allowed for either party after delivery.
        $allowedForRole = match (true) {
            $isSeller && ! $isBuyer => ['dispatched', 'delivered', 'refunded', 'cancelled'],
            $isBuyer && ! $isSeller => ['cancelled', 'refunded'],
            default => Order::STATUSES,
        };
        abort_unless(in_array($target, $allowedForRole, true), 403, 'Not allowed for your role.');

        // Buyer cancellation only before dispatch.
        if ($isBuyer && ! $isSeller && $target === 'cancelled') {
            abort_unless($order->status === 'awaiting_dispatch', 422, 'Too late to cancel.');
        }

        abort_unless($order->canTransitionTo($target), 422, 'Invalid status transition.');

        $order->recordTransition($target, $data['note'] ?? null);

        // Keep listing status in sync: cancelled orders free the listing again.
        if ($target === 'cancelled' && $order->listing) {
            $order->listing->update(['status' => 'active']);
        }

        $order->load(['buyer:id,name,location', 'seller:id,name,location', 'listing:id,title,photos']);
        $role = $order->buyer_id === $user->id ? 'buyer' : 'seller';

        return response()->json(['order' => $this->payload($order, $role)]);
    }

    /** Mock delivery pricing — mirrors the prototype's assumptions. */
    private function deliveryCost(Listing $listing, ?string $mode): int
    {
        $mode = $mode ?: $listing->delivery_mode;
        if ($mode === 'pickup') {
            return 0;
        }

        return $listing->location === 'Lagos' ? 1500 : 3000;
    }

    private function payload(Order $order, string $role = 'buyer'): array
    {
        return [
            'id' => 'api-'.$order->id,
            'listing_id' => $order->listing_id,
            'title' => $order->title,
            'price' => $order->price,
            'delivery_cost' => $order->delivery_cost,
            'fee' => $order->fee,
            'total' => $order->total,
            'status' => $order->status,
            'address' => $order->address,
            'payment_method' => $order->payment_method,
            'simulated' => true,
            'role' => $role,
            'created_at' => $order->created_at?->toIso8601String(),
            'history' => $order->status_history ?? [],
            'buyer' => $order->buyer ? ['id' => $order->buyer->id, 'name' => $order->buyer->name] : null,
            'seller' => $order->seller ? ['id' => $order->seller->id, 'name' => $order->seller->name] : null,
            'listing' => $order->listing ? [
                'id' => $order->listing->id,
                'title' => $order->listing->title,
                'photos' => $order->listing->photos ?? [],
            ] : null,
        ];
    }
}
