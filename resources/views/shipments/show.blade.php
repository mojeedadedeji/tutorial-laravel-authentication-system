@extends('layouts.app')

@section('title', 'Shipment ' . $shipment->reference . ' - ' . config('app.name'))

@section('content')
    <style>
        .status-badge { font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 999px; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-in_transit { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-delayed { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #e5e7eb; color: #374151; }
        .panel { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05); }
        .timeline { list-style: none; padding: 0; margin: 0; }
        .timeline li { position: relative; padding-left: 1.75rem; padding-bottom: 1rem; }
        .timeline li::before {
            content: ''; position: absolute; left: 0.4rem; top: 0.35rem;
            width: 0.6rem; height: 0.6rem; border-radius: 50%; background: #2563eb;
        }
        .timeline li::after {
            content: ''; position: absolute; left: 0.65rem; top: 1rem; bottom: -0.25rem;
            width: 2px; background: #e5e7eb;
        }
        .timeline li:last-child::after { display: none; }
        .timeline .t-label { font-weight: 600; font-size: 0.875rem; }
        .timeline .t-time { font-size: 0.75rem; color: #6c757d; }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">{{ $shipment->reference }}</h1>
            <p class="text-secondary mb-0">{{ $shipment->origin }} → {{ $shipment->destination }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-outline-primary btn-sm">Edit</a>
            <a href="{{ route('shipments.invoice', $shipment) }}" class="btn btn-outline-secondary btn-sm" target="_blank">Invoice</a>
            <a href="{{ route('shipments.index') }}" class="btn btn-outline-secondary btn-sm">← All shipments</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card panel mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0 fw-bold">Details</h2>
                        <span class="status-badge status-{{ $shipment->status }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6"><div class="text-secondary small">Origin</div><div class="fw-semibold">{{ $shipment->origin }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Destination</div><div class="fw-semibold">{{ $shipment->destination }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Weight</div><div class="fw-semibold">{{ $shipment->weight_kg ? number_format($shipment->weight_kg) . ' kg' : '—' }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Amount</div><div class="fw-semibold">₦{{ number_format($shipment->amount) }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Shipped</div><div class="fw-semibold">{{ $shipment->shipped_at?->format('M j, Y') ?? '—' }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Delivered</div><div class="fw-semibold">{{ $shipment->delivered_at?->format('M j, Y') ?? '—' }}</div></div>
                    </div>
                </div>
            </div>

            <div class="card panel">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3 fw-bold">Status timeline</h2>
                    <ul class="timeline">
                        @foreach ($timeline as $step)
                            <li>
                                <div class="t-label">{{ $step['label'] }}</div>
                                <div class="t-time">{{ $step['at'] }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card panel mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3 fw-bold">Update status</h2>
                    <form method="POST" action="{{ route('shipments.status', $shipment) }}">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select mb-3" required>
                            @foreach (\App\Models\Shipment::STATUSES as $s)
                                <option value="{{ $s }}" @selected($shipment->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary w-100">Update status</button>
                    </form>
                </div>
            </div>

            <div class="card panel">
                <div class="card-body p-4">
                    <h2 class="h6 mb-2 fw-bold text-danger">Danger zone</h2>
                    <p class="text-secondary small mb-3">Deleting a shipment cannot be undone.</p>
                    <form method="POST" action="{{ route('shipments.destroy', $shipment) }}" onsubmit="return confirm('Delete this shipment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">Delete shipment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
