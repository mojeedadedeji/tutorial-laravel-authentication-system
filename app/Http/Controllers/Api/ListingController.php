<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    /**
     * List active listings with marketplace filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', Rule::in(array_merge(['all'], Listing::CATEGORIES))],
            'condition' => ['nullable', Rule::in(array_merge(['any'], Listing::CONDITIONS))],
            'brand' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:80'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', Rule::in(['newest', 'price-asc', 'price-desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $listings = Listing::query()
            ->active()
            ->with('user:id,name,location')
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 24)
            ->withQueryString();

        return response()->json($listings);
    }

    /**
     * Show a single listing.
     */
    public function show(Listing $listing): JsonResponse
    {
        $listing->load('user:id,name,location,created_at');

        return response()->json(['listing' => $this->payload($listing)]);
    }

    /**
     * Create a listing (authenticated sellers only).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'active';

        $listing = Listing::create($data);
        $listing->load('user:id,name,location');

        return response()->json(['listing' => $this->payload($listing)], 201);
    }

    /**
     * Update the authenticated user's own listing.
     */
    public function update(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->update($this->validated($request, partial: true));
        $listing->load('user:id,name,location');

        return response()->json(['listing' => $this->payload($listing)]);
    }

    /**
     * Delete the authenticated user's own listing.
     */
    public function destroy(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->delete();

        return response()->json(['message' => 'Listing deleted.']);
    }

    /**
     * Mark the authenticated user's listing as sold.
     */
    public function markSold(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->update(['status' => 'sold']);

        return response()->json(['listing' => $this->payload($listing->fresh('user'))]);
    }

    /**
     * List the authenticated user's own listings (any status).
     */
    public function mine(Request $request): JsonResponse
    {
        $listings = $request->user()
            ->listings()
            ->with('user:id,name,location')
            ->latest()
            ->get()
            ->map(fn (Listing $l) => $this->payload($l));

        return response()->json(['listings' => $listings]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'title' => [$required, 'string', 'min:5', 'max:120'],
            'description' => [$required, 'string', 'min:20', 'max:2000'],
            'price' => [$required, 'integer', 'min:1', 'max:100000000'],
            'category' => [$required, Rule::in(Listing::CATEGORIES)],
            'condition' => [$required, Rule::in(Listing::CONDITIONS)],
            'brand' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:80'],
            'location' => [$required, 'string', 'max:80'],
            'delivery_mode' => ['nullable', Rule::in(Listing::DELIVERY_MODES)],
            'delivery_note' => ['nullable', 'string', 'max:160'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['string', 'max:500'],
        ]);
    }

    private function payload(Listing $listing): array
    {
        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'category' => $listing->category,
            'condition' => $listing->condition,
            'brand' => $listing->brand,
            'size' => $listing->size,
            'location' => $listing->location,
            'delivery_mode' => $listing->delivery_mode,
            'delivery_note' => $listing->delivery_note,
            'photos' => $listing->photos ?? [],
            'status' => $listing->status,
            'created_at' => $listing->created_at?->toIso8601String(),
            'seller' => $listing->user ? [
                'id' => $listing->user->id,
                'name' => $listing->user->name,
                'location' => $listing->user->location,
            ] : null,
        ];
    }
}
