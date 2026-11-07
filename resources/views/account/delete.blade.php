@extends('layouts.app')

@section('title', 'Delete account - ' . config('app.name'))

@section('content')
    <div class="card" style="max-width: 32rem;">
        <h1 class="text-danger">Delete account</h1>
        <p class="muted">Once you delete your account, there is no going back. Please enter your password to confirm.</p>

        <div class="alert alert-error" role="alert" style="background: #fef2f2; color: #991b1b;">
            This will permanently delete your account, profile information, and all associated data.
        </div>

        <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you sure you want to permanently delete your account? This cannot be undone.');">
            @csrf
            @method('DELETE')

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                @error('password')
                    <div class="text-danger" style="font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn" style="background: #dc2626;">Delete account</button>
            <a href="{{ route('dashboard') }}" class="btn" style="background: #6b7280; margin-left: 0.5rem;">Cancel</a>
        </form>
    </div>
@endsection
