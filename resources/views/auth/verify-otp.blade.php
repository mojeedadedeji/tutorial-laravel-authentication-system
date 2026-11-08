@extends('layouts.guest')

@section('title', 'Verify phone - ' . config('app.name'))
@section('heading', 'Verify your phone')

@section('content')
    <p style="font-size:0.875rem; color:#4b5563; margin-top:-0.5rem;">
        Enter the 6-digit code we sent to
        <strong>{{ session('registration.pending.phone') }}</strong>.
    </p>

    <form method="POST" action="{{ route('register.verify.store') }}">
        @csrf

        <div class="field">
            <label for="otp">Verification code</label>
            <input id="otp" type="text" name="otp" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" autofocus placeholder="123456">
        </div>

        <button type="submit" class="btn">Verify &amp; create account</button>
    </form>

    <div class="links">
        <form method="POST" action="{{ route('register.resend') }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:none;border:none;color:#2563eb;font-size:0.875rem;cursor:pointer;padding:0;">Resend code</button>
        </form>
        &middot;
        <a href="{{ route('register') }}">Start over</a>
    </div>
@endsection
