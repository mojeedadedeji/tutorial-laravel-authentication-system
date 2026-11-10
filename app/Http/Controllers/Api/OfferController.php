<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Offer;
use App\Notifications\OfferReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferController extends Controller
{
    /**
     * Buyer makes an offer on an active listing.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $listing = Listing::with('user')->findOrFail($data['listing_id']);

        abort_if($listing->status !== 'active', 422, 'This item is no longer available.');
        abort_if($listing->user_id === $request->user()->id, 422, 'You cannot offer on your own listing.');
        abort_if($data['amount'] >= $listing->price, 422, 'Offer must be below the listed price.');

        // One pending offer per buyer per listing.
        $existing = Offer::where('listing_id', $listing->id)
            ->where('buyer_id', $request->user()->id)
            ->whereIn('status', ['pending', 'countered'])
            ->exists();
        abort_if($existing, 422, 'You already have an open offer on this item.');

        $offer = Offer::create([
            'listing_id' => $listing->id,
            'buyer_id' => $request->user()->id,
            'seller_id' => $listing->user_id,
            'amount' => $data['amount'],
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        $listing->user->notify(new OfferReceived($offer));

        return response()->json(['offer' => $this->payload($offer->load(['listing', 'buyer', 'seller']))], 201);
    }

    /** Offers the current user has made (as buyer). */
    public function made(Request $request): JsonResponse
    {
        $offers = Offer::where('buyer_id', $request->user()->id)
            ->with(['listing:id,title,price,photos,status', 'seller:id,name,location'])
            ->latest()
            ->get()
            ->map(fn (Offer $o) => $this->payload($o));

        return response()->json(['offers' => $offers]);
    }

    /** Offers received on the current user's listings (as seller). */
    public function received(Request $request): JsonResponse
    {
        $offers = Offer::where('seller_id', $request->user()->id)
            ->with(['listing:id,title,price,photos,status', 'buyer:id,name,location'])
            ->latest()
            ->get()
            ->map(fn (Offer $o) => $this->payload($o));

        return response()->json(['offers' => $offers]);
    }

    /** Seller accepts — listing price drops to the offer amount. */
    public function accept(Request $request, Offer $offer): JsonResponse
    {
        $this->authorizeSeller($request, $offer);
        abort_unless(in_array($offer->status, ['pending', 'countered'], true), 422, 'Offer is no longer open.');

        $amount = $offer->status === 'countered' && $offer->counter_amount
            ? $offer->counter_amount
            : $offer->amount;

        $offer->listing?->update(['price' => $amount]);
        $offer->update(['status' => 'accepted', 'counter_amount' => null]);

        $offer->buyer->notify(new OfferReceived($offer->fresh(), 'accepted'));

        return response()->json(['offer' => $this->payload($offer->fresh(['listing', 'buyer', 'seller']))]);
    }

    /** Seller declines. */
    public function decline(Request $request, Offer $offer): JsonResponse
    {
        $this->authorizeSeller($request, $offer);
        abort_unless(in_array($offer->status, ['pending', 'countered'], true), 422, 'Offer is no longer open.');

        $offer->update(['status' => 'declined', 'counter_amount' => null]);
        $offer->buyer->notify(new OfferReceived($offer->fresh(), 'declined'));

        return response()->json(['offer' => $this->payload($offer->fresh(['listing', 'buyer', 'seller']))]);
    }

    /** Seller counters with a different amount. */
    public function counter(Request $request, Offer $offer): JsonResponse
    {
        $this->authorizeSeller($request, $offer);
        $data = $request->validate(['amount' => ['required', 'integer', 'min:1']]);
        abort_unless(in_array($offer->status, ['pending', 'countered'], true), 422, 'Offer is no longer open.');
        abort_if($data['amount'] >= $offer->listing->price, 422, 'Counter must be below the listed price.');

        $offer->update(['status' => 'countered', 'counter_amount' => $data['amount']]);
        $offer->buyer->notify(new OfferReceived($offer->fresh(), 'countered'));

        return response()->json(['offer' => $this->payload($offer->fresh(['listing', 'buyer', 'seller']))]);
    }

    /** Buyer cancels their own open offer. */
    public function cancel(Request $request, Offer $offer): JsonResponse
    {
        abort_unless($offer->buyer_id === $request->user()->id, 403);
        abort_unless(in_array($offer->status, ['pending', 'countered'], true), 422, 'Offer is no longer open.');

        $offer->update(['status' => 'cancelled', 'counter_amount' => null]);

        return response()->json(['offer' => $this->payload($offer->fresh(['listing', 'buyer', 'seller']))]);
    }

    /** Buyer accepts a seller's counter — listing price drops to counter amount. */
    public function acceptCounter(Request $request, Offer $offer): JsonResponse
    {
        abort_unless($offer->buyer_id === $request->user()->id, 403);
        abort_unless($offer->status === 'countered' && $offer->counter_amount, 422, 'No counter to accept.');

        $offer->listing?->update(['price' => $offer->counter_amount]);
        $offer->update(['status' => 'accepted', 'counter_amount' => null]);
        $offer->seller->notify(new OfferReceived($offer->fresh(), 'accepted'));

        return response()->json(['offer' => $this->payload($offer->fresh(['listing', 'buyer', 'seller']))]);
    }

    private function authorizeSeller(Request $request, Offer $offer): void
    {
        abort_unless($offer->seller_id === $request->user()->id, 403);
    }

    private function payload(Offer $offer): array
    {
        $listing = $offer->listing;

        return [
            'id' => 'api-'.$offer->id,
            'listing_id' => $offer->listing_id,
            'amount' => $offer->amount,
            'counter_amount' => $offer->counter_amount,
            'message' => $offer->message,
            'status' => $offer->status,
            'created_at' => $offer->created_at?->toIso8601String(),
            'listing' => $listing ? [
                'id' => 'api-'.$listing->id,
                'title' => $listing->title,
                'price' => $listing->price,
                'photos' => $listing->photos ?? [],
                'status' => $listing->status,
            ] : null,
            'buyer' => $offer->buyer ? ['id' => $offer->buyer->id, 'name' => $offer->buyer->name] : null,
            'seller' => $offer->seller ? ['id' => $offer->seller->id, 'name' => $offer->seller->name] : null,
        ];
    }
}
