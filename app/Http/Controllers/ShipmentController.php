<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Notifications\ShipmentStatusUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Show a single shipment with its status timeline.
     */
    public function show(Request $request, Shipment $shipment): View
    {
        abort_unless($shipment->user_id === $request->user()->id, 403);

        return view('shipments.show', [
            'shipment' => $shipment,
            'timeline' => $this->timeline($shipment),
        ]);
    }

    /**
     * Update a shipment's status.
     */
    public function updateStatus(Request $request, Shipment $shipment): RedirectResponse
    {
        abort_unless($shipment->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Shipment::STATUSES)],
        ]);

        $shipment->update([
            'status' => $validated['status'],
            'delivered_at' => $validated['status'] === 'delivered' && ! $shipment->delivered_at
                ? now()
                : $shipment->delivered_at,
        ]);

        $shipment->user->notify(new ShipmentStatusUpdated($shipment));

        return back()->with('status', 'Shipment status updated.');
    }

    /**
     * Show the edit shipment form.
     */
    public function edit(Request $request, Shipment $shipment): View
    {
        abort_unless($shipment->user_id === $request->user()->id, 403);

        return view('shipments.edit', [
            'shipment' => $shipment,
            'cities' => $this->cityOptions($request->user()->id),
        ]);
    }

    /**
     * Update a shipment's details.
     */
    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        abort_unless($shipment->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:100'],
            'destination' => ['required', 'string', 'max:100', 'different:origin'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.01', 'max:1000000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:' . implode(',', Shipment::STATUSES)],
            'shipped_at' => ['nullable', 'date'],
        ]);

        $shipment->update([
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'weight_kg' => $validated['weight_kg'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'shipped_at' => $validated['shipped_at'] ?? $shipment->shipped_at,
            'delivered_at' => $validated['status'] === 'delivered' && ! $shipment->delivered_at
                ? now()
                : $shipment->delivered_at,
        ]);

        return redirect()->route('shipments.show', $shipment)->with('status', 'Shipment updated.');
    }

    /**
     * Delete a shipment.
     */
    public function destroy(Request $request, Shipment $shipment): RedirectResponse
    {
        abort_unless($shipment->user_id === $request->user()->id, 403);

        $shipment->delete();

        return redirect()->route('shipments.index')->with('status', 'Shipment deleted.');
    }

    /**
     * Export the user's shipments as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $shipments = Shipment::where('user_id', $request->user()->id)
            ->latest('shipped_at')
            ->get();

        return response()->streamDownload(function () use ($shipments) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Origin', 'Destination', 'Status', 'Weight (kg)', 'Amount', 'Shipped At', 'Delivered At']);
            foreach ($shipments as $s) {
                fputcsv($out, [
                    $s->reference,
                    $s->origin,
                    $s->destination,
                    $s->status,
                    $s->weight_kg,
                    $s->amount,
                    $s->shipped_at?->toDateTimeString(),
                    $s->delivered_at?->toDateTimeString(),
                ]);
            }
            fclose($out);
        }, 'shipments-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Show a printable invoice for a shipment.
     */
    public function invoice(Request $request, Shipment $shipment): View
    {
        abort_unless($shipment->user_id === $request->user()->id, 403);

        return view('shipments.invoice', [
            'shipment' => $shipment,
            'user' => $request->user(),
        ]);
    }

    /**
     * Build the status timeline for a shipment.
     */
    private function timeline(Shipment $shipment): array
    {
        $steps = [
            ['label' => 'Created', 'at' => $shipment->created_at],
            ['label' => 'Shipped', 'at' => $shipment->shipped_at],
            ['label' => 'Delivered', 'at' => $shipment->delivered_at],
        ];

        return collect($steps)
            ->filter(fn ($s) => $s['at'] !== null)
            ->map(fn ($s) => ['label' => $s['label'], 'at' => $s['at']->format('M j, Y g:i A')])
            ->values()
            ->all();
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
