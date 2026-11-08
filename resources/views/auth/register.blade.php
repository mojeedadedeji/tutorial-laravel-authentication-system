@extends('layouts.guest')

@section('title', 'Register - ' . config('app.name'))
@section('heading', 'Create an account')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        </div>

        <div class="field">
            <label for="phone">Phone number</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="e.g. 08012345678">
            <small style="color:#6b7280;">We'll send a verification code to this number.</small>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="pw-wrap">
                <input id="password" type="password" name="password" required autocomplete="new-password">
                <button type="button" class="pw-toggle" data-pw-toggle="password" aria-pressed="false" aria-label="Show password">Show</button>
            </div>
            <ul class="req-list" id="pw-requirements" aria-label="Password requirements">
                <li data-rule="length">At least 8 characters</li>
                <li data-rule="letters">At least one letter</li>
                <li data-rule="numbers">At least one number</li>
            </ul>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <div class="pw-wrap">
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                <button type="button" class="pw-toggle" data-pw-toggle="password_confirmation" aria-pressed="false" aria-label="Show password">Show</button>
            </div>
            <span class="match-hint" id="pw-match" role="status" aria-live="polite"></span>
        </div>

        <button type="submit" class="btn">Register</button>
    </form>

    <div class="links">
        <a href="{{ route('login') }}">Already have an account? Log in</a>
    </div>
@endsection
