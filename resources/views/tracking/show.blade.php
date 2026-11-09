<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track {{ $shipment->reference }} — 5050 Logistics Limited</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1f2937; }
        .topbar { background: #0b1f3a; padding: 1rem 0; }
        .topbar img { height: 2.25rem; }
        .card-main { border: none; border-radius: 1rem; box-shadow: 0 4px 24px rgb(0 0 0 / 0.08); }
        .status-pill { font-size: 0.8125rem; font-weight: 700; padding: 0.4rem 1rem; border-radius: 999px; }
        .st-pending { background: #fef3c7; color: #92400e; }
        .st-in_transit { background: #dbeafe; color: #1e40af; }
        .st-delivered { background: #d1fae5; color: #065f46; }
        .st-delayed { background: #fee2e2; color: #991b1b; }
        .st-cancelled { background: #e5e7eb; color: #374151; }
        .step { display: flex; gap: 1rem; padding: 0.75rem 0; }
        .step-dot { width: 2rem; height: 2rem; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; }
        .step-dot.done { background: #198754; }
        .step-dot.pending { background: #e5e7eb; color: #6c757d; }
        .step-label { font-weight: 600; }
        .step-time { font-size: 0.8125rem; color: #6c757d; }
    </style>
</head>
<body>
<nav class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="{{ url('/') }}"><img src="{{ asset('img/logo-white.svg') }}" alt="5050 Logistics Limited"></a>
        <a href="{{ url('/') }}" class="btn btn-sm" style="background: #f59e0b; color: #111827; font-weight: 600;">Get a quote</a>
    </div>
</nav>

<div class="container py-5" style="max-width: 44rem;">
    <div class="card card-main">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <div class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Shipment reference</div>
                    <h1 class="h3 fw-bold mb-0">{{ $shipment->reference }}</h1>
                </div>
                <span class="status-pill st-{{ $shipment->status }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3"><div class="text-secondary small">Origin</div><div class="fw-semibold">{{ $shipment->origin }}</div></div>
                <div class="col-6 col-md-3"><div class="text-secondary small">Destination</div><div class="fw-semibold">{{ $shipment->destination }}</div></div>
                <div class="col-6 col-md-3"><div class="text-secondary small">Weight</div><div class="fw-semibold">{{ $shipment->weight_kg ? number_format($shipment->weight_kg) . ' kg' : '—' }}</div></div>
                <div class="col-6 col-md-3"><div class="text-secondary small">Shipped</div><div class="fw-semibold">{{ $shipment->shipped_at?->format('M j, Y') ?? '—' }}</div></div>
            </div>

            <h2 class="h6 fw-bold mb-3">Tracking timeline</h2>
            @php
                $labels = [
                    'pending' => 'Shipment created',
                    'in_transit' => 'In transit',
                    'delivered' => 'Delivered',
                    'delayed' => 'Delayed',
                    'cancelled' => 'Cancelled',
                ];
                $history = $shipment->statusHistories;
            @endphp
            @forelse ($history as $i => $entry)
                @php $isLatest = $i === $history->count() - 1; @endphp
                <div class="step">
                    <div class="step-dot {{ $entry->status === 'delivered' ? 'done' : ($entry->status === 'cancelled' ? 'pending' : ($isLatest ? 'done' : 'pending')) }}">{{ $i + 1 }}</div>
                    <div>
                        <div class="step-label">{{ $labels[$entry->status] ?? ucfirst(str_replace('_', ' ', $entry->status)) }}</div>
                        <div class="step-time">{{ $entry->created_at->format('M j, Y g:i A') }}</div>
                        @if ($entry->note)<div class="step-time fst-italic">{{ $entry->note }}</div>@endif
                    </div>
                </div>
            @empty
                <p class="text-secondary small">No tracking updates yet.</p>
            @endforelse

            @if ($shipment->status === 'delayed')
                <div class="alert alert-warning mt-3 mb-0" role="alert">
                    ⚠️ This shipment is experiencing a delay. Our team has been notified.
                </div>
            @endif
            @if ($shipment->status === 'cancelled')
                <div class="alert alert-secondary mt-3 mb-0" role="alert">
                    This shipment has been cancelled.
                </div>
            @endif
        </div>
    </div>

    <div class="text-center mt-4">
        <p class="text-secondary small mb-2">Need help with this shipment?</p>
        <a href="https://wa.me/2347066496268" class="btn btn-success" target="_blank" rel="noopener">Chat on WhatsApp</a>
    </div>
</div>

<footer class="text-center py-4 text-secondary small">
    © {{ date('Y') }} 5050 Logistics Limited · 12 Marina Road, Lagos · +234 706 649 6268
</footer>
</body>
</html>
