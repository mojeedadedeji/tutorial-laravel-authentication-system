@extends('layouts.app')

@section('title', 'Profile - ' . config('app.name'))

@section('content')
    <div class="card" style="max-width: 28rem;">
        <h1 style="font-size: 1.25rem; font-weight: 600; margin: 0 0 0.25rem;">Profile</h1>
        <p class="muted" style="margin-top: 0;">Member since {{ $user->created_at->format('F j, Y') }}</p>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
            </div>

            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            </div>

            <div class="field">
                <label for="phone">Contact phone number</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="+234 800 000 0000">
            </div>

            <div class="field">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3" autocomplete="street-address" placeholder="Street, city, state, country" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; font-family: inherit;">{{ old('address', $user->address) }}</textarea>
            </div>

            <button type="submit" class="btn">Save changes</button>
        </form>
    </div>
@endsection
