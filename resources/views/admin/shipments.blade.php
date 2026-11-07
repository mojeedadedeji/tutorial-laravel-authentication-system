@extends('layouts.app')

@section('title', 'All shipments - Admin - ' . config('app.name'))

@section('content')
    <style>
        .status-badge { font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 999px; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-in_transit { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-delayed { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #e5e7eb; color: #374151; }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">All shipments</h1>
            <p class="text-secondary mb-0">{{ $shipments->total() }} shipments across all customers</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Admin dashboard</a>
    </div>

    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card mb-4" style="border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold mb-1">Search reference</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="SHP-XXXXXX">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2"><button class="btn btn-primary btn-sm w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card" style="border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Reference</th><th>Customer</th><th>Route</th><th>Vehicle</th><th>Status</th><th class="text-end">Amount</th><th>Update</th></tr></thead>
                <tbody>
                    @forelse ($shipments as $s)
                        <tr>
                            <td class="fw-semibold">{{ $s->reference }}</td>
                            <td>{{ $s->user->name }}<br><small class="text-secondary">{{ $s->user->email }}</small></td>
                            <td>{{ $s->origin }} → {{ $s->destination }}</td>
                            <td>{{ $s->vehicle?->name ?? '—' }}</td>
                            <td><span class="status-badge status-{{ $s->status }}">{{ ucfirst(str_replace('_', ' ', $s->status)) }}</span></td>
                            <td class="text-end">₦{{ number_format($s->amount) }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.shipments.status', $s) }}" class="d-flex gap-1">
                                    @csrf @method('PATCH')
                                    <select name="vehicle_id" class="form-select form-select-sm" style="width:auto;" title="Assign vehicle" onchange="this.form.submit()">
                                        <option value="">No vehicle</option>
                                        @foreach ($vehicles as $v)
                                            <option value="{{ $v->id }}" @selected($s->vehicle_id === $v->id)>{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st }}" @selected($s->status === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-5">No shipments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($shipments->hasPages())
            <div class="card-body border-top">{{ $shipments->links() }}</div>
        @endif
    </div>
@endsection
