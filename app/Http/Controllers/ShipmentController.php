<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

        $cities = Shipment::where('user_id', $request->user()->id)
            ->select('origin')
            ->distinct()
            ->pluck('origin')
            ->merge(
                Shipment::where('user_id', $request->user()->id)
                    ->select('destination')
                    ->distinct()
                    ->pluck('destination')
            )
            ->unique()
            ->sort()
            ->values();

        return view('shipments.index', [
            'shipments' => $shipments,
            'filters' => $filters,
            'cities' => $cities,
        ]);
    }
}
