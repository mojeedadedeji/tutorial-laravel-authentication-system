@extends('layouts.app')

@section('title', 'Fleet - Admin - ' . config('app.name'))

@section('content')
    <style>
        .v-badge { font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 999px; }
        .v-available { background: #d1fae5; color: #065f46; }
        .v-on_trip { background: #dbeafe; color: #1e40af; }
        .v-maintenance { background: #fef3c7; color: #92400e; }
        .v-retired { background: #e5e7eb; color: #374151; }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Fleet management</h1>
            <p class="text-secondary mb-0">{{ $vehicles->total() }} vehicles</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary btn-sm">+ Add vehicle</a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Admin</a>
        </div>
    </div>

    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card" style="border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Name</th><th>Type</th><th>Plate</th><th>Driver</th><th>Status</th><th>Shipments</th><th></th></tr></thead>
                <tbody>
                    @forelse ($vehicles as $v)
                        <tr>
                            <td class="fw-semibold">{{ $v->name }}</td>
                            <td class="text-capitalize">{{ $v->type }}</td>
                            <td>{{ $v->plate_number ?? '—' }}</td>
                            <td>{{ $v->driver_name ?? '—' }}@if($v->driver_phone)<br><small class="text-secondary">{{ $v->driver_phone }}</small>@endif</td>
                            <td><span class="v-badge v-{{ $v->status }}">{{ ucfirst(str_replace('_', ' ', $v->status)) }}</span></td>
                            <td>{{ $v->shipments_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.vehicles.edit', $v) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.vehicles.destroy', $v) }}" class="d-inline" onsubmit="return confirm('Remove this vehicle?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-5">No vehicles yet. Add your first one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($vehicles->hasPages())
            <div class="card-body border-top">{{ $vehicles->links() }}</div>
        @endif
    </div>
@endsection
