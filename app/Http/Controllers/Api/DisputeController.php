<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Notifications\DisputeOpened;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    /**
     * Open a dispute on an order (buyer protection). Either party may open;
     * only one open dispute per order.
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $user = $request->user();
        abort_unless($order->buyer_id === $user->id || $order->seller_id === $user->id, 403);
        abort_unless(in_array($order->status, ['dispatched', 'delivered'], true), 422, 'Disputes can only be opened on dispatched or delivered orders.');

        $existing = Dispute::where('order_id', $order->id)->where('status', 'open')->exists();
        abort_if($existing, 422, 'A dispute is already open on this order.');

        $dispute = Dispute::create([
            'order_id' => $order->id,
            'opener_id' => $user->id,
            'reason' => $data['reason'],
            'status' => 'open',
        ]);

        // Notify the other party.
        $other = $order->buyer_id === $user->id ? $order->seller : $order->buyer;
        $other?->notify(new DisputeOpened($dispute->load('order')));

        return response()->json(['dispute' => $this->payload($dispute->fresh(['opener', 'order']))], 201);
    }

    /** Disputes the current user opened or is party to. */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $disputes = Dispute::whereHas('order', fn ($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->orWhere('opener_id', $userId)
            ->with(['opener:id,name', 'order:id,title,status,buyer_id,seller_id'])
            ->latest()
            ->get()
            ->map(fn (Dispute $d) => $this->payload($d));

        return response()->json(['disputes' => $disputes]);
    }

    /** Opener withdraws an open dispute. */
    public function close(Request $request, Dispute $dispute): JsonResponse
    {
        abort_unless($dispute->opener_id === $request->user()->id, 403);
        abort_unless($dispute->status === 'open', 422, 'Dispute is already closed.');

        $dispute->update(['status' => 'closed', 'resolution_note' => 'Withdrawn by opener']);

        return response()->json(['dispute' => $this->payload($dispute->fresh(['opener', 'order']))]);
    }

    private function payload(Dispute $dispute): array
    {
        return [
            'id' => 'api-'.$dispute->id,
            'order_id' => $dispute->order_id,
            'reason' => $dispute->reason,
            'status' => $dispute->status,
            'resolution_note' => $dispute->resolution_note,
            'created_at' => $dispute->created_at?->toIso8601String(),
            'opener' => $dispute->opener ? ['id' => $dispute->opener->id, 'name' => $dispute->opener->name] : null,
            'order' => $dispute->order ? [
                'id' => $dispute->order->id,
                'title' => $dispute->order->title,
                'status' => $dispute->order->status,
            ] : null,
        ];
    }
}
