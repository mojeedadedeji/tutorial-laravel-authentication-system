@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
    <style>
        .kpi-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgb(0 0 0 / 0.12); }
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
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-primary-subtle text-primary">▣</div>
                    <div>
                        <div class="kpi-value">48.6K</div>
                        <div class="kpi-label">Shipments</div>
                        <div class="kpi-delta delta-up">▲ 9.7% vs last year</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-success-subtle text-success">✓</div>
                    <div>
                        <div class="kpi-value">96.2%</div>
                        <div class="kpi-label">On-time delivery</div>
                        <div class="kpi-delta delta-up">▲ 1.4 pts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-warning-subtle text-warning">%</div>
                    <div>
                        <div class="kpi-value">78.4%</div>
                        <div class="kpi-label">Fleet utilization</div>
                        <div class="kpi-delta delta-down">▼ 3.1 pts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-info-subtle text-info">$</div>
                    <div>
                        <div class="kpi-value">$2.14M</div>
                        <div class="kpi-label">Freight revenue</div>
                        <div class="kpi-delta delta-up">▲ 14.2% YoY</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card panel h-100">
                <div class="panel-header-custom">
                    <div>
                        <h2 class="panel-title">Logistics performance</h2>
                        <div class="panel-sub">Monthly shipments &amp; on-time delivery — trailing 12 months (dummy data)</div>
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
                        <div class="panel-sub">By shipment volume this quarter</div>
                    </div>
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
                            <tr>
                                <td><span class="rank-badge bg-primary">1</span></td>
                                <td>Lagos → Abuja</td>
                                <td class="text-end fw-semibold">8,420</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-primary" style="width: 100%"></div></div><span class="small">22%</span></div></td>
                            </tr>
                            <tr>
                                <td><span class="rank-badge bg-secondary">2</span></td>
                                <td>Port Harcourt → Kano</td>
                                <td class="text-end fw-semibold">6,910</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-secondary" style="width: 82%"></div></div><span class="small">18%</span></div></td>
                            </tr>
                            <tr>
                                <td><span class="rank-badge bg-warning text-dark">3</span></td>
                                <td>Ibadan → Lagos</td>
                                <td class="text-end fw-semibold">5,340</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-warning" style="width: 63%"></div></div><span class="small">14%</span></div></td>
                            </tr>
                            <tr>
                                <td><span class="rank-badge bg-info">4</span></td>
                                <td>Enugu → Abuja</td>
                                <td class="text-end fw-semibold">4,120</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-info" style="width: 49%"></div></div><span class="small">11%</span></div></td>
                            </tr>
                            <tr>
                                <td><span class="rank-badge bg-light text-dark border">5</span></td>
                                <td>Benin City → Lagos</td>
                                <td class="text-end fw-semibold">3,280</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-light" style="width: 39%"></div></div><span class="small">9%</span></div></td>
                            </tr>
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
    var months = ['Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'];
    var shipments = [3.4, 3.1, 3.8, 3.6, 4.2, 4.4, 4.9, 5.1, 4.8, 5.3, 5.7, 6.1];
    var ontime = [93.1, 92.4, 94.0, 93.6, 94.8, 95.1, 95.6, 96.0, 95.4, 96.1, 96.4, 96.2];

    var datasets = {
        shipments: {
            label: 'Shipments (K)',
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
