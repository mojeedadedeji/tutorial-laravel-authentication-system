@extends('layouts.app')

@section('title', 'Users - Admin - ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Users</h1>
            <p class="text-secondary mb-0">{{ $users->total() }} registered users</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Admin dashboard</a>
    </div>

    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card mb-4" style="border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold mb-1">Search name or email</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-2"><button class="btn btn-primary btn-sm w-100">Search</button></div>
            </form>
        </div>
    </div>

    <div class="card" style="border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Shipments</th><th>Payment methods</th><th>Role</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td class="fw-semibold">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->shipments_count }}</td>
                            <td>{{ $u->payment_methods_count }}</td>
                            <td>{!! $u->is_admin ? '<span class="badge bg-primary">Admin</span>' : '<span class="badge bg-secondary">User</span>' !!}</td>
                            <td>{{ $u->created_at->format('M j, Y') }}</td>
                            <td>
                                @if ($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-admin', $u) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm {{ $u->is_admin ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                                            {{ $u->is_admin ? 'Revoke admin' : 'Make admin' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-secondary small">You</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-5">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-body border-top">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
