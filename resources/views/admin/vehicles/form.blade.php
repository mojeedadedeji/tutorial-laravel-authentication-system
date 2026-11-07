@extends('layouts.app')

@section('title', ($vehicle->exists ? 'Edit' : 'Add') . ' vehicle - Admin - ' . config('app.name'))

@section('content')
    <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0 fw-bold">{{ $vehicle->exists ? 'Edit vehicle' : 'Add vehicle' }}</h1>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary btn-sm">← Fleet</a>
    </div>

    <div class="card" style="max-width: 36rem; border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="card-body p-4">
            <form method="POST" action="{{ $vehicle->exists ? route('admin.vehicles.update', $vehicle) : route('admin.vehicles.store') }}">
                @csrf
                @if ($vehicle->exists) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $vehicle->name) }}" required placeholder="Truck 01">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Type</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach ($types as $t)
                                <option value="{{ $t }}" @selected(old('type', $vehicle->type ?? 'van') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Plate number</label>
                        <input type="text" name="plate_number" class="form-control" value="{{ old('plate_number', $vehicle->plate_number) }}" placeholder="LAG-123-XX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            @foreach ($statuses as $s)
                                <option value="{{ $s }}" @selected(old('status', $vehicle->status ?? 'available') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Driver name</label>
                        <input type="text" name="driver_name" class="form-control" value="{{ old('driver_name', $vehicle->driver_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Driver phone</label>
                        <input type="text" name="driver_phone" class="form-control" value="{{ old('driver_phone', $vehicle->driver_phone) }}">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">{{ $vehicle->exists ? 'Save changes' : 'Add vehicle' }}</button>
                    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
