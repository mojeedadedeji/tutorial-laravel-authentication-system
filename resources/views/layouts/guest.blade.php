<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f3f4f6;
            color: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            width: 100%;
            max-width: 24rem;
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            padding: 2rem;
        }
        h1 { font-size: 1.25rem; font-weight: 600; margin: 0 0 1.5rem; text-align: center; }
        .field { margin-bottom: 1rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        input:focus { outline: 2px solid #2563eb; outline-offset: 1px; border-color: #2563eb; }
        .checkbox { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; margin-bottom: 1rem; }
        .btn {
            width: 100%;
            padding: 0.5rem 1rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
        }
        .btn:hover { background: #1d4ed8; }
        .btn:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
        .alert {
            padding: 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-error ul { margin: 0; padding-left: 1.25rem; }
        .links { margin-top: 1rem; text-align: center; font-size: 0.875rem; }
        .links a { color: #2563eb; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 3.5rem; }
        .pw-toggle {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #2563eb;
            font-size: 0.8125rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
        }
        .pw-toggle:hover { text-decoration: underline; }
        .pw-toggle:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; border-radius: 0.25rem; }
        .req-list {
            list-style: none;
            margin: 0.25rem 0 0.75rem;
            padding: 0;
            font-size: 0.8125rem;
        }
        .req-list li { padding: 0.125rem 0 0.125rem 1.25rem; position: relative; color: #6b7280; }
        .req-list li::before { content: "\2717"; position: absolute; left: 0; color: #dc2626; }
        .req-list li.req-pass { color: #166534; }
        .req-list li.req-pass::before { content: "\2713"; color: #16a34a; }
        .match-hint { display: block; font-size: 0.8125rem; margin-top: 0.25rem; min-height: 1.1em; }
        .match-ok { color: #166534; }
        .match-bad { color: #991b1b; }
    </style>
</head>
<body>
    <main class="card">
        <div style="text-align: center; margin-bottom: 1.25rem;">
            <img src="{{ asset('img/logo.svg') }}" alt="5050 Logistics Limited" style="height: 3rem; width: auto;">
        </div>
        <h1>@yield('heading')</h1>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
    <script src="{{ asset('js/auth-password.js') }}" defer></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}" defer></script>
</body>
</html>
