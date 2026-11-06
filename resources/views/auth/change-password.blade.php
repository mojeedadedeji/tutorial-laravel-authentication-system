@extends('layouts.app')

@section('title', 'Change password - ' . config('app.name'))

@section('content')
    <div class="card" style="max-width: 28rem;">
        <h1 style="font-size: 1.25rem; font-weight: 600; margin: 0 0 1.5rem;">Change password</h1>

        <form method="POST" action="{{ route('password.change') }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="current_password">Current password</label>
            <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
        </div>

        <div class="field">
            <label for="password">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn">Update password</button>
        </form>
    </div>
@endsection
