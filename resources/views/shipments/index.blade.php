@extends('layouts.app')

@section('title', 'Shipments - ' . config('app.name'))

@section('content')
    <style>
        .status-badge { font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 999px; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-in_transit { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-delayed { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #e5e7eb; color: #374151; }
        .filter-card { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05); }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Shipments</h1>
            <p class="text-secondary mb-0">{{ $shipments->total() }} shipment{{ $shipments->total() === 1 ? '' : 's' }} found</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
    </div>

    <div class="card filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('shipments.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="q" class="form-label small fw-semibold mb-1">Reference</label>
                    <input type="text" class="form-control" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="SHP-123456">
                </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label small fw-semibold mb-1">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All</option>
                        @foreach (\App\Models\Shipment::STATUSES as $s)
                            <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="origin" class="form-label small fw-semibold mb-1">Origin</label>
                    <select class="form-select" id="origin" name="origin">
                        <option value="">All</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city }}" @selected(($filters['origin'] ?? '') === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="destination" class="form-label small fw-semibold mb-1">Destination</label>
                    <select class="form-select" id="destination" name="destination">
                        <option value="">All</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city }}" @selected(($filters['destination'] ?? '') === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="month" class="form-label small fw-semibold mb-1">Month</label>
                    <input type="month" class="form-control" id="month" name="month" value="{{ $filters['month'] ?? '' }}">
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <a href="{{ route('shipments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Lane</th>
                        <th>Status</th>
                        <th class="text-end">Weight</th>
                        <th class="text-end">Amount</th>
                        <th>Shipped</th>
                        <th>Delivered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shipments as $shipment)
                        <tr>
                            <td class="fw-semibold">{{ $shipment->reference }}</td>
                            <td>{{ $shipment->origin }} → {{ $shipment->destination }}</td>
                            <td><span class="status-badge status-{{ $shipment->status }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span></td>
                            <td class="text-end">{{ $shipment->weight_kg ? number_format($shipment->weight_kg) . ' kg' : '—' }}</td>
                            <td class="text-end fw-semibold">₦{{ number_format($shipment->amount) }}</td>
                            <td>{{ $shipment->shipped_at?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $shipment->delivered_at?->format('M j, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-5">No shipments match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($shipments->hasPages())
            <div class="p-3">{{ $shipments->links() }}</div>
        @endif
    </div>
@endsection
