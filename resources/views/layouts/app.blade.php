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
            background: #f8fafc;
            color: #111827;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 16.5rem;
            background: #111827;
            color: #d1d5db;
            display: flex;
            flex-direction: column;
            z-index: 1030;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 0.625rem;
            padding: 1.25rem 1.5rem;
            color: #f9fafb;
            font-weight: 700;
            font-size: 1.0625rem;
            text-decoration: none;
            border-bottom: 1px solid rgb(255 255 255 / 0.08);
        }
        .sidebar-brand .brand-mark {
            width: 2rem; height: 2rem;
            border-radius: 0.5rem;
            background: #2563eb;
            color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.875rem; font-weight: 700;
        }
        .sidebar-nav { flex: 1; padding: 1rem 0.75rem; display: flex; flex-direction: column; gap: 0.125rem; }
        .sidebar-nav .nav-section {
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            padding: 0.75rem 0.75rem 0.375rem;
        }
        .sidebar-nav a, .sidebar-nav button.sidebar-link {
            display: flex; align-items: center; gap: 0.75rem;
            width: 100%;
            padding: 0.625rem 0.75rem;
            border-radius: 0.5rem;
            color: #d1d5db;
            text-decoration: none;
            font-size: 0.875rem;
            background: none;
            border: none;
            text-align: left;
            cursor: pointer;
        }
        .sidebar-nav a:hover, .sidebar-nav button.sidebar-link:hover {
            background: rgb(255 255 255 / 0.07);
            color: #fff;
        }
        .sidebar-nav a[aria-current="page"] {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
        }
        .sidebar-nav a.nav-danger { color: #fca5a5; }
        .sidebar-nav a.nav-danger:hover { background: rgb(220 38 38 / 0.2); color: #fecaca; }
        .sidebar-nav a.nav-danger[aria-current="page"] { background: #dc2626; color: #fff; }
        .sidebar-nav .nav-icon { width: 1.125rem; text-align: center; flex-shrink: 0; }
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgb(255 255 255 / 0.08);
        }
        .sidebar-user { font-size: 0.8125rem; color: #f9fafb; font-weight: 600; }
        .sidebar-user-email { font-size: 0.75rem; color: #9ca3af; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sidebar-logout {
            margin-top: 0.75rem;
            width: 100%;
            padding: 0.5rem;
            background: rgb(255 255 255 / 0.08);
            border: 1px solid rgb(255 255 255 / 0.15);
            color: #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            cursor: pointer;
        }
        .sidebar-logout:hover { background: rgb(255 255 255 / 0.14); color: #fff; }

        /* Mobile toggler */
        .sidebar-toggler {
            display: none;
            position: fixed;
            top: 0.875rem; left: 0.875rem;
            z-index: 1040;
            background: #111827;
            border: none;
            color: #fff;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 1.125rem;
            line-height: 1;
        }

        /* Main content */
        .main-content {
            margin-left: 16.5rem;
            min-height: 100vh;
            padding: 2rem 2rem 3rem;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.06);
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

        /* Mobile: sidebar becomes offcanvas */
        @media (max-width: 991.98px) {
            .sidebar-toggler { display: block; }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 4rem 1rem 2rem; }
            .sidebar-backdrop {
                position: fixed; inset: 0;
                background: rgb(0 0 0 / 0.5);
                z-index: 1020;
                display: none;
            }
            .sidebar-backdrop.show { display: block; }
        }
    </style>
</head>
<body>
    <button type="button" class="sidebar-toggler" id="sidebarToggler" aria-controls="sidebar" aria-expanded="false" aria-label="Open menu">☰</button>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <aside class="sidebar" id="sidebar" aria-label="Main navigation">
        <a class="sidebar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('img/logo-white.svg') }}" alt="5050 Logistics Limited" style="height: 2.75rem; width: auto;">
        </a>

        <nav class="sidebar-nav">
            <div class="nav-section">Menu</div>
            <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">▦</span> Dashboard
            </a>
            <a href="{{ route('profile.edit') }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">◉</span> Profile
            </a>
            <a href="{{ route('password.edit') }}" @if (request()->routeIs('password.edit')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">⚿</span> Change password
            </a>
            <a href="{{ route('payment-methods.index') }}" @if (request()->routeIs('payment-methods.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">💳</span> Payment methods
            </a>
            <a href="{{ route('account.delete') }}" class="nav-danger" @if (request()->routeIs('account.delete')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">✕</span> Delete account
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">{{ auth()->user()->name }}</div>
            <div class="sidebar-user-email">{{ auth()->user()->email }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout">Log out</button>
            </form>
        </div>
    </aside>

    <main class="main-content">
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
    <script>
    (function () {
        var toggler = document.getElementById('sidebarToggler');
        var sidebar = document.getElementById('sidebar');
        var backdrop = document.getElementById('sidebarBackdrop');
        if (!toggler || !sidebar) return;

        function setOpen(open) {
            sidebar.classList.toggle('show', open);
            backdrop.classList.toggle('show', open);
            toggler.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggler.addEventListener('click', function () {
            setOpen(!sidebar.classList.contains('show'));
        });
        backdrop.addEventListener('click', function () { setOpen(false); });
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () { setOpen(false); });
        });
    })();
    </script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}" defer></script>
    @yield('scripts')
</body>
</html>
