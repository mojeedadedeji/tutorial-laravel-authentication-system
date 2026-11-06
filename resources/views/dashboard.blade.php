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
        .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
        .kpi-label { font-size: 0.8125rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.04em; }
        .kpi-delta { font-size: 0.8125rem; font-weight: 600; }
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
            <p class="text-secondary mb-0">Welcome back, {{ auth()->user()->name }} — here's how the hair care market is performing.</p>
        </div>
        <span class="badge text-bg-light border">{{ now()->format('l, F j, Y') }}</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-primary-subtle text-primary">$</div>
                    <div>
                        <div class="kpi-value">$1.28M</div>
                        <div class="kpi-label">Market revenue</div>
                        <div class="kpi-delta delta-up">▲ 12.4% vs last year</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-success-subtle text-success">#</div>
                    <div>
                        <div class="kpi-value">84.2K</div>
                        <div class="kpi-label">Units sold</div>
                        <div class="kpi-delta delta-up">▲ 8.1% vs last year</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-warning-subtle text-warning">%</div>
                    <div>
                        <div class="kpi-value">23.6%</div>
                        <div class="kpi-label">Market share</div>
                        <div class="kpi-delta delta-up">▲ 1.8 pts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-info-subtle text-info">↗</div>
                    <div>
                        <div class="kpi-value">+18.9%</div>
                        <div class="kpi-label">YoY growth</div>
                        <div class="kpi-delta delta-down">▼ 2.3 pts vs Q3</div>
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
                        <h2 class="panel-title">Hair care market sales</h2>
                        <div class="panel-sub">Monthly revenue &amp; units sold — trailing 12 months (dummy data)</div>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Chart metric">
                        <button type="button" class="btn btn-outline-primary active" data-metric="revenue">Revenue</button>
                        <button type="button" class="btn btn-outline-primary" data-metric="units">Units</button>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="salesChart" aria-label="Hair care market sales chart" role="img"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card panel h-100">
                <div class="panel-header-custom">
                    <div>
                        <h2 class="panel-title">Top products</h2>
                        <div class="panel-sub">By revenue this quarter</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Product</th>
                                <th scope="col" class="text-end">Revenue</th>
                                <th scope="col" class="text-end">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="rank-badge bg-primary">1</span></td>
                                <td>HydraRepair Shampoo 500ml</td>
                                <td class="text-end fw-semibold">$312K</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-primary" style="width: 100%"></div></div><span class="small">24%</span></div></td>
                            </tr>
                            <tr>
                                <td><span class="rank-badge bg-secondary">2</span></td>
                                <td>Keratin Smooth Conditioner</td>
                                <td class="text-end fw-semibold">$268K</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-secondary" style="width: 86%"></div></div><span class="small">21%</span></div></td>
                            </tr>
                            <tr>
                                <td><span class="rank-badge bg-warning text-dark">3</span></td>
                                <td>Argan Oil Hair Serum</td>
                                <td class="text-end fw-semibold">$201K</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-warning" style="width: 64%"></div></div><span class="small">16%</span></div></td>
                            </tr>
                            <tr>
                                <td><span class="rank-badge bg-info">4</span></td>
                                <td>Volume Boost Mousse</td>
                                <td class="text-end fw-semibold">$154K</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-info" style="width: 49%"></div></div><span class="small">12%</span></div></td>
                            </tr>
                            <tr>
                                <td><span class="rank-badge bg-light text-dark border">5</span></td>
                                <td>Scalp Detox Exfoliant</td>
                                <td class="text-end fw-semibold">$118K</td>
                                <td class="text-end"><div class="d-flex align-items-center justify-content-end gap-2"><div class="progress flex-grow-1" style="max-width: 4rem;"><div class="progress-bar bg-light" style="width: 38%"></div></div><span class="small">9%</span></div></td>
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
    var revenue = [82, 74, 91, 88, 105, 112, 124, 131, 128, 142, 156, 168];
    var units = [6.1, 5.4, 6.8, 6.5, 7.9, 8.4, 9.2, 9.8, 9.5, 10.6, 11.7, 12.4];

    var datasets = {
        revenue: {
            label: 'Revenue ($K)',
            data: revenue,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 6
        },
        units: {
            label: 'Units sold (K)',
            data: units,
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
            datasets: [datasets.revenue]
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
