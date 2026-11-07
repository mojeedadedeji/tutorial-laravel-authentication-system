<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $shipment->reference }} — 5050 Logistics Limited</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1f2937; }
        .invoice { background: #fff; border-radius: 1rem; box-shadow: 0 4px 24px rgb(0 0 0 / 0.08); max-width: 48rem; margin: 0 auto; }
        .inv-header { border-bottom: 2px solid #0b1f3a; padding-bottom: 1.5rem; }
        .inv-header img { height: 2.5rem; }
        .inv-table th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6c757d; }
        .inv-total { font-size: 1.5rem; font-weight: 800; color: #0b1f3a; }
        @media print { body { background: #fff; } .no-print { display: none; } .invoice { box-shadow: none; border-radius: 0; } }
    </style>
</head>
<body class="py-5">
<div class="container">
    <div class="no-print mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-outline-secondary btn-sm">← Back to shipment</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">🖨 Print / Save PDF</button>
    </div>

    <div class="invoice p-4 p-md-5">
        <div class="inv-header d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <img src="{{ asset('img/logo.svg') }}" alt="5050 Logistics Limited">
                <div class="text-secondary small mt-1">12 Marina Road, Lagos, Nigeria<br>+234 706 649 6268 · info@fiftyfiftylogistics.com</div>
            </div>
            <div class="text-end">
                <div class="h4 fw-bold mb-0">INVOICE</div>
                <div class="text-secondary">{{ $shipment->reference }}</div>
                <div class="text-secondary small">Date: {{ now()->format('M j, Y') }}</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="text-secondary small text-uppercase fw-semibold">Bill to</div>
                <div class="fw-semibold">{{ $user->name }}</div>
                <div class="text-secondary small">{{ $user->email }}</div>
                @if ($user->phone)<div class="text-secondary small">{{ $user->phone }}</div>@endif
                @if ($user->address)<div class="text-secondary small">{{ $user->address }}</div>@endif
            </div>
            <div class="col-md-6 text-md-end">
                <div class="text-secondary small text-uppercase fw-semibold">Shipment details</div>
                <div><strong>{{ $shipment->origin }}</strong> → <strong>{{ $shipment->destination }}</strong></div>
                <div class="text-secondary small">Status: {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</div>
                <div class="text-secondary small">Shipped: {{ $shipment->shipped_at?->format('M j, Y') ?? '—' }}</div>
            </div>
        </div>

        <table class="table inv-table">
            <thead>
                <tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Amount</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Freight — {{ $shipment->origin }} to {{ $shipment->destination }}
                        @if ($shipment->weight_kg)<br><small class="text-secondary">Weight: {{ number_format($shipment->weight_kg) }} kg</small>@endif
                    </td>
                    <td class="text-end">1</td>
                    <td class="text-end">₦{{ number_format($shipment->amount) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end fw-bold">Total due</td>
                    <td class="text-end inv-total">₦{{ number_format($shipment->amount) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="text-secondary small mt-4">
            Payment methods accepted: Visa, Mastercard, Verve, Paystack, OPay. Pay within 7 days to avoid service interruption.
        </div>
    </div>
</div>
</body>
</html>
