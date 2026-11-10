<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Notifications\ReviewReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Leave a review for the other party of a delivered order.
     * One review per user per order.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::findOrFail($data['order_id']);
        $user = $request->user();

        abort_unless($order->buyer_id === $user->id || $order->seller_id === $user->id, 403);
        abort_unless($order->status === 'delivered', 422, 'You can only review after delivery.');

        $revieweeId = $order->buyer_id === $user->id ? $order->seller_id : $order->buyer_id;

        $review = Review::updateOrCreate(
            ['order_id' => $order->id, 'reviewer_id' => $user->id],
            ['reviewee_id' => $revieweeId, 'rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        $review->reviewee->notify(new ReviewReceived($review));

        return response()->json(['review' => $this->payload($review->fresh(['reviewer', 'reviewee']))], 201);
    }

    /** Reviews received by a user, plus their average rating. */
    public function forUser(Request $request, int $userId): JsonResponse
    {
        $reviews = Review::where('reviewee_id', $userId)
            ->with(['reviewer:id,name', 'order:id,title'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Review $r) => $this->payload($r));

        $avg = Review::where('reviewee_id', $userId)->avg('rating');
        $count = Review::where('reviewee_id', $userId)->count();

        return response()->json([
            'reviews' => $reviews,
            'rating' => $avg ? round((float) $avg, 1) : null,
            'count' => $count,
        ]);
    }

    /** Whether the current user has already reviewed a given order. */
    public function forOrder(Request $request, Order $order): JsonResponse
    {
        $review = Review::where('order_id', $order->id)
            ->where('reviewer_id', $request->user()->id)
            ->first();

        return response()->json(['review' => $review ? $this->payload($review) : null]);
    }

    private function payload(Review $review): array
    {
        return [
            'id' => 'api-'.$review->id,
            'order_id' => $review->order_id,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'created_at' => $review->created_at?->toIso8601String(),
            'reviewer' => $review->reviewer ? ['id' => $review->reviewer->id, 'name' => $review->reviewer->name] : null,
            'reviewee' => $review->reviewee ? ['id' => $review->reviewee->id, 'name' => $review->reviewee->name] : null,
        ];
    }
}
