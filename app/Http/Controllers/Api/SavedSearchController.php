<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    /** List the current user's saved searches. */
    public function index(Request $request): JsonResponse
    {
        $searches = SavedSearch::where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (SavedSearch $s) => $this->payload($s));

        return response()->json(['saved_searches' => $searches]);
    }

    /** Save the current browse filters as a named search. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'filters' => ['required', 'array'],
            'filters.query' => ['nullable', 'string', 'max:120'],
            'filters.category' => ['nullable', 'string', 'max:40'],
            'filters.min_price' => ['nullable', 'integer', 'min:0'],
            'filters.max_price' => ['nullable', 'integer', 'min:0'],
            'filters.condition' => ['nullable', 'string', 'max:40'],
            'filters.location' => ['nullable', 'string', 'max:80'],
            'notify' => ['nullable', 'boolean'],
        ]);

        $search = SavedSearch::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'filters' => $data['filters'],
            'notify' => $data['notify'] ?? true,
        ]);

        return response()->json(['saved_search' => $this->payload($search)], 201);
    }

    /** Delete a saved search (owner only). */
    public function destroy(Request $request, SavedSearch $savedSearch): JsonResponse
    {
        abort_unless($savedSearch->user_id === $request->user()->id, 403);
        $savedSearch->delete();

        return response()->json(['message' => 'Saved search deleted.']);
    }

    private function payload(SavedSearch $s): array
    {
        return [
            'id' => 'api-'.$s->id,
            'name' => $s->name,
            'filters' => $s->filters,
            'notify' => $s->notify,
            'created_at' => $s->created_at?->toIso8601String(),
        ];
    }
}
