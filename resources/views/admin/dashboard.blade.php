@extends('layouts.app')

@section('title', 'Admin dashboard - ' . config('app.name'))

@section('content')
    <style>
        .stat-card { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05); }
        .stat-value { font-size: 1.75rem; font-weight: 800; }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Admin dashboard</h1>
            <p class="text-secondary mb-0">Platform overview</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">← User dashboard</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['Users', $stats['users'], '👥'],
            ['Shipments', $stats['shipments'], '📦'],
            ['Revenue', '₦' . number_format($stats['revenue']), '💰'],
            ['In transit', $stats['in_transit'], '🚚'],
            ['Delivered', $stats['delivered'], '✅'],
            ['Vehicles', $stats['vehicles'], '🚛'],
            ['Blog posts', $stats['posts'], '📝'],
        ] as [$label, $value, $icon])
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="text-secondary small">{{ $icon }} {{ $label }}</div>
                        <div class="stat-value">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card" style="border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0 fw-bold">Recent shipments</h2>
                <a href="{{ route('admin.shipments') }}" class="btn btn-outline-primary btn-sm">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Reference</th><th>Customer</th><th>Route</th><th>Status</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @foreach ($recentShipments as $s)
                            <tr>
                                <td class="fw-semibold">{{ $s->reference }}</td>
                                <td>{{ $s->user->name }}</td>
                                <td>{{ $s->origin }} → {{ $s->destination }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $s->status)) }}</td>
                                <td class="text-end">₦{{ number_format($s->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
