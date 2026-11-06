@extends('layouts.guest')

@section('title', 'Forgot password - ' . config('app.name'))
@section('heading', 'Forgot password')

@section('content')
    <p style="font-size: 0.875rem; color: #4b5563; margin-top: 0;">
        Enter your email address and we will send you a password reset link.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <button type="submit" class="btn">Email reset link</button>
    </form>

    <div class="links">
        <a href="{{ route('login') }}">Back to log in</a>
    </div>
@endsection
