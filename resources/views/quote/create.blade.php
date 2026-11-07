<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Get a quote — 5050 Logistics Limited</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1f2937; }
        .topbar { background: #0b1f3a; padding: 1rem 0; }
        .topbar img { height: 2.25rem; }
        .card-main { border: none; border-radius: 1rem; box-shadow: 0 4px 24px rgb(0 0 0 / 0.08); }
        .quote-total { font-size: 2rem; font-weight: 800; color: #0b1f3a; }
    </style>
</head>
<body>
<nav class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="{{ url('/') }}"><img src="{{ asset('img/logo-white.svg') }}" alt="5050 Logistics Limited"></a>
        <a href="{{ url('/') }}" class="btn btn-sm btn-outline-light">← Home</a>
    </div>
</nav>

<div class="container py-5" style="max-width: 42rem;">
    <h1 class="h3 fw-bold mb-1">Shipping quote calculator</h1>
    <p class="text-secondary mb-4">Instant estimate for your shipment — no signup required.</p>

    <div class="card card-main">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('quote.calculate') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="origin">Origin</label>
                        <input type="text" class="form-control" id="origin" name="origin" list="cities" value="{{ old('origin', $quote['origin'] ?? '') }}" required placeholder="e.g. Lagos">
                        <datalist id="cities">
                            @foreach ($cities as $city)<option value="{{ $city }}">@endforeach
                        </datalist>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="destination">Destination</label>
                        <select class="form-select" id="destination" name="destination" required>
                            <option value="">Select destination…</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" @selected(old('destination', $quote['destination'] ?? '') === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="weight_kg">Weight (kg)</label>
                        <input type="number" class="form-control" id="weight_kg" name="weight_kg" value="{{ old('weight_kg', $quote['weight_kg'] ?? '') }}" min="0.01" step="0.01" required placeholder="e.g. 500">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4 px-4">Calculate quote</button>
            </form>
        </div>
    </div>

    @isset($quote)
        <div class="card card-main mt-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Your estimate</h2>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Route</span><span class="fw-semibold">{{ $quote['origin'] }} → {{ $quote['destination'] }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Weight</span><span class="fw-semibold">{{ number_format($quote['weight_kg']) }} kg</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Base fare</span><span>₦{{ number_format($quote['base_fare']) }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Freight ({{ number_format($quote['rate_per_kg']) }}/kg)</span><span>₦{{ number_format($quote['freight']) }}</span></div>
                <div class="d-flex justify-content-between py-3"><span class="fw-bold">Estimated total</span><span class="quote-total">₦{{ number_format($quote['total']) }}</span></div>
                <div class="alert alert-info mb-0" role="alert">🚚 Estimated delivery: <strong>{{ $quote['eta_days'] }}–{{ $quote['eta_days'] + 2 }} days</strong>. Final price confirmed on booking.</div>
            </div>
        </div>
    @endisset
</div>
</body>
</html>
