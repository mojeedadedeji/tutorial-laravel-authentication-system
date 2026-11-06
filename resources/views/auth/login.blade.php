@extends('layouts.guest')

@section('title', 'Log in - ' . config('app.name'))
@section('heading', 'Log in')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="pw-wrap">
                <input id="password" type="password" name="password" required autocomplete="current-password">
                <button type="button" class="pw-toggle" data-pw-toggle="password" aria-pressed="false" aria-label="Show password">Show</button>
            </div>
        </div>

        <div class="checkbox">
            <input id="remember" type="checkbox" name="remember">
            <label for="remember" style="margin: 0; font-weight: 400;">Remember me</label>
        </div>

        <button type="submit" class="btn">Log in</button>
    </form>

    <div class="links">
        <a href="{{ route('password.request') }}">Forgot your password?</a>
        <span aria-hidden="true">&middot;</span>
        <a href="{{ route('register') }}">Create an account</a>
    </div>
@endsection
