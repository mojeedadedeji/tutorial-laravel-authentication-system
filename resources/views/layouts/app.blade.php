<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f3f4f6;
            color: #111827;
            min-height: 100vh;
        }
        .topnav {
            background: #1f2937;
            color: #f9fafb;
        }
        .topnav-inner {
            max-width: 64rem;
            margin: 0 auto;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .brand { font-weight: 600; font-size: 1rem; color: #f9fafb; text-decoration: none; }
        .nav { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
        .nav a {
            color: #d1d5db;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .nav a:hover { color: #fff; text-decoration: underline; }
        .nav a[aria-current="page"] { color: #fff; font-weight: 600; }
        .logout-btn {
            background: none;
            border: 1px solid #4b5563;
            color: #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            cursor: pointer;
        }
        .logout-btn:hover { color: #fff; border-color: #9ca3af; }
        main { max-width: 64rem; margin: 0 auto; padding: 2rem 1.5rem; }
        .card {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            padding: 2rem;
        }
        h1 { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.5rem; }
        .muted { color: #4b5563; font-size: 0.9375rem; }
        .alert {
            padding: 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-error ul { margin: 0; padding-left: 1.25rem; }
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
        .btn {
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
    </style>
</head>
<body>
    <header class="topnav">
        <div class="topnav-inner">
            <a class="brand" href="{{ route('dashboard') }}">{{ config('app.name') }}</a>
            <nav class="nav" aria-label="Main navigation">
                <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                <a href="{{ route('password.edit') }}" @if (request()->routeIs('password.edit')) aria-current="page" @endif>Change password</a>
                <a href="{{ route('profile.edit') }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif>Profile</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Log out</button>
                </form>
            </nav>
        </div>
    </header>

    <main>
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
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}" defer></script>
    @yield('scripts')
</body>
</html>
