@extends('layouts.app')

@section('title', 'Edit shipment - ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Edit shipment</h1>
            <p class="text-secondary mb-0">{{ $shipment->reference }}</p>
        </div>
        <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-outline-secondary btn-sm">← Back to shipment</a>
    </div>

    <div class="card" style="max-width: 40rem; border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('shipments.update', $shipment) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="origin" class="form-label fw-semibold">Origin</label>
                        <input type="text" class="form-control @error('origin') is-invalid @enderror" id="origin" name="origin" list="cities" value="{{ old('origin', $shipment->origin) }}" required>
                        <datalist id="cities">
                            @foreach ($cities as $city)
                                <option value="{{ $city }}">
                            @endforeach
                        </datalist>
                        @error('origin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="destination" class="form-label fw-semibold">Destination</label>
                        <input type="text" class="form-control @error('destination') is-invalid @enderror" id="destination" name="destination" list="cities" value="{{ old('destination', $shipment->destination) }}" required>
                        @error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="weight_kg" class="form-label fw-semibold">Weight (kg)</label>
                        <input type="number" class="form-control @error('weight_kg') is-invalid @enderror" id="weight_kg" name="weight_kg" value="{{ old('weight_kg', $shipment->weight_kg) }}" min="0.01" step="0.01">
                        @error('weight_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label fw-semibold">Amount (₦)</label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $shipment->amount) }}" min="0" step="0.01" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            @foreach (\App\Models\Shipment::STATUSES as $s)
                                <option value="{{ $s }}" @selected(old('status', $shipment->status) === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="shipped_at" class="form-label fw-semibold">Ship date</label>
                        <input type="date" class="form-control @error('shipped_at') is-invalid @enderror" id="shipped_at" name="shipped_at" value="{{ old('shipped_at', $shipment->shipped_at?->toDateString()) }}">
                        @error('shipped_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">Save changes</button>
                    <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
