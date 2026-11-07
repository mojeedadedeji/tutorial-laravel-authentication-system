@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
    <style>
        .kpi-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            color: inherit;
            text-decoration: none;
            display: block;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgb(0 0 0 / 0.12); color: inherit; }
        .kpi-icon {
            width: 3rem; height: 3rem;
            border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        .kpi-value { font-size: 1.3rem; font-weight: 700; line-height: 1.2; }
        .kpi-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.04em; }
        .kpi-delta { font-size: 0.75rem; font-weight: 600; }
        .delta-up { color: #198754; }
        .delta-down { color: #dc3545; }
        .delta-flat { color: #6c757d; }
        .panel {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);
        }
        .panel-header-custom {
            padding: 1.25rem 1.5rem 0;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;
        }
        .panel-title { font-weight: 600; font-size: 1rem; margin: 0; }
        .panel-sub { font-size: 0.8125rem; color: #6c757d; }
        .chart-wrap { position: relative; height: 320px; padding: 1rem 1.5rem 1.5rem; }
        .table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6c757d; border-bottom-width: 1px; }
        .rank-badge {
            width: 1.75rem; height: 1.75rem; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; color: #fff;
        }
        .lane-row { cursor: pointer; }
        .lane-row:hover { background: #f8fafc; }
        @media (max-width: 768px) { .chart-wrap { height: 260px; } }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Dashboard</h1>
            <p class="text-secondary mb-0">Welcome back, {{ auth()->user()->name }} — here's how logistics operations are performing.</p>
        </div>
        <span class="badge text-bg-light border">{{ now()->format('l, F j, Y') }}</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('shipments.index') }}" class="text-decoration-none text-reset">
                <div class="card kpi-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-primary-subtle text-primary">▣</div>
                        <div>
                            <div class="kpi-value">{{ number_format($kpis['shipments']) }}</div>
                            <div class="kpi-label">Shipments this month</div>
                            @if ($kpis['shipments_delta'] !== null)
                                <div class="kpi-delta {{ $kpis['shipments_delta'] >= 0 ? 'delta-up' : 'delta-down' }}">
                                    {{ $kpis['shipments_delta'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['shipments_delta']) }}% vs last month
                                </div>
                            @else
                                <div class="kpi-delta delta-flat">No prior-month data</div>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('shipments.index', ['status' => 'delivered']) }}" class="text-decoration-none text-reset">
                <div class="card kpi-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-success-subtle text-success">✓</div>
                        <div>
                            <div class="kpi-value">{{ $kpis['ontime'] }}%</div>
                            <div class="kpi-label">On-time delivery</div>
                            <div class="kpi-delta {{ $kpis['ontime_delta'] >= 0 ? 'delta-up' : 'delta-down' }}">
                                {{ $kpis['ontime_delta'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['ontime_delta']) }} pts vs last month
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('shipments.index', ['status' => 'in_transit']) }}" class="text-decoration-none text-reset">
                <div class="card kpi-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-warning-subtle text-warning">🚚</div>
                        <div>
                            <div class="kpi-value">{{ number_format($kpis['in_transit']) }}</div>
                            <div class="kpi-label">In transit now</div>
                            <div class="kpi-delta {{ $kpis['delayed'] > 0 ? 'delta-down' : 'delta-flat' }}">
                                {{ $kpis['delayed'] }} delayed
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('shipments.index') }}" class="text-decoration-none text-reset">
                <div class="card kpi-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-info-subtle text-info">$</div>
                        <div>
                            <div class="kpi-value">₦{{ number_format($kpis['revenue'] / 1500, 1) }}K</div>
                            <div class="kpi-label">Freight revenue (mo)</div>
                            @if ($kpis['revenue_delta'] !== null)
                                <div class="kpi-delta {{ $kpis['revenue_delta'] >= 0 ? 'delta-up' : 'delta-down' }}">
                                    {{ $kpis['revenue_delta'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['revenue_delta']) }}% vs last month
                                </div>
                            @else
                                <div class="kpi-delta delta-flat">No prior-month data</div>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card panel h-100">
                <div class="panel-header-custom">
                    <div>
                        <h2 class="panel-title">Logistics performance</h2>
                        <div class="panel-sub">Monthly shipments &amp; on-time delivery — trailing 12 months</div>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Chart metric">
                        <button type="button" class="btn btn-outline-primary active" data-metric="shipments">Shipments</button>
                        <button type="button" class="btn btn-outline-primary" data-metric="ontime">On-time %</button>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="salesChart" aria-label="Logistics performance chart" role="img"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card panel h-100">
                <div class="panel-header-custom">
                    <div>
                        <h2 class="panel-title">Busiest lanes</h2>
                        <div class="panel-sub">By shipment volume — last 90 days</div>
                    </div>
                    <a href="{{ route('shipments.index') }}" class="small">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Lane</th>
                                <th scope="col" class="text-end">Shipments</th>
                                <th scope="col" class="text-end">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topLanes as $i => $lane)
                                @php $share = round($lane->total / $laneTotal * 100); @endphp
                                <tr class="lane-row" onclick="window.location='{{ route('shipments.index', ['origin' => $lane->origin, 'destination' => $lane->destination]) }}'">
                                    <td><span class="rank-badge {{ $i === 0 ? 'bg-primary' : ($i === 1 ? 'bg-secondary' : ($i === 2 ? 'bg-warning text-dark' : 'bg-light text-dark border')) }}">{{ $i + 1 }}</span></td>
                                    <td>{{ $lane->origin }} → {{ $lane->destination }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($lane->total) }}</td>
                                    <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar {{ $i === 0 ? 'bg-primary' : ($i === 1 ? 'bg-secondary' : 'bg-info') }}" style="width: {{ $share }}%"></div></div><span class="small">{{ $share }}%</span></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-4">No shipments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var months = @json($chartLabels);
    var shipments = @json($chartShipments);
    var ontime = @json($chartOnTime);

    var datasets = {
        shipments: {
            label: 'Shipments',
            data: shipments,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 6
        },
        ontime: {
            label: 'On-time delivery (%)',
            data: ontime,
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 6
        }
    };

    var ctx = document.getElementById('salesChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [datasets.shipments]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (item) {
                            return item.dataset.label + ': ' + item.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#6c757d' } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { color: '#6c757d' }
                }
            }
        }
    });

    document.querySelectorAll('[data-metric]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('[data-metric]').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            chart.data.datasets = [datasets[btn.getAttribute('data-metric')]];
            chart.update();
        });
    });
});
</script>
@endsection
