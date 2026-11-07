<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    /**
     * List the user's shipments with optional filters.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:' . implode(',', Shipment::STATUSES)],
            'origin' => ['nullable', 'string', 'max:100'],
            'destination' => ['nullable', 'string', 'max:100'],
            'month' => ['nullable', 'date_format:Y-m'],
            'q' => ['nullable', 'string', 'max:50'],
        ]);

        $query = Shipment::where('user_id', $request->user()->id)
            ->latest('shipped_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['origin'])) {
            $query->where('origin', $filters['origin']);
        }

        if (! empty($filters['destination'])) {
            $query->where('destination', $filters['destination']);
        }

        if (! empty($filters['month'])) {
            $query->whereYear('shipped_at', substr($filters['month'], 0, 4))
                ->whereMonth('shipped_at', substr($filters['month'], 5, 2));
        }

        if (! empty($filters['q'])) {
            $query->where('reference', 'like', '%' . $filters['q'] . '%');
        }

        $shipments = $query->paginate(15)->withQueryString();

        return view('shipments.index', [
            'shipments' => $shipments,
            'filters' => $filters,
            'cities' => $this->cityOptions($request->user()->id),
        ]);
    }

    /**
     * Show the create shipment form.
     */
    public function create(): View
    {
        return view('shipments.create', [
            'cities' => $this->cityOptions(auth()->id()),
        ]);
    }

    /**
     * Store a new shipment for the user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:100'],
            'destination' => ['required', 'string', 'max:100', 'different:origin'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.01', 'max:1000000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:' . implode(',', Shipment::STATUSES)],
            'shipped_at' => ['nullable', 'date'],
        ]);

        $request->user()->shipments()->create([
            'reference' => 'SHP-' . strtoupper(Str::random(8)),
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'weight_kg' => $validated['weight_kg'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'shipped_at' => $validated['shipped_at'] ?? now(),
            'delivered_at' => $validated['status'] === 'delivered'
                ? ($validated['shipped_at'] ? \Illuminate\Support\Carbon::parse($validated['shipped_at'])->addDays(2) : now()->addDays(2))
                : null,
        ]);

        return redirect()->route('shipments.index')->with('status', 'Shipment created.');
    }

    /**
     * Distinct cities used across the user's shipments.
     */
    private function cityOptions(int $userId)
    {
        return Shipment::where('user_id', $userId)
            ->select('origin')
            ->distinct()
            ->pluck('origin')
            ->merge(
                Shipment::where('user_id', $userId)
                    ->select('destination')
                    ->distinct()
                    ->pluck('destination')
            )
            ->unique()
            ->sort()
            ->values();
    }
}
