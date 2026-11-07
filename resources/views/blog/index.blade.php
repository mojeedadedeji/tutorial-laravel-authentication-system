<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blog — 5050 Logistics Limited</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1f2937; }
        .topbar { background: #0b1f3a; padding: 1rem 0; }
        .topbar img { height: 2.25rem; }
        .post-card { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05); transition: transform 0.15s ease; }
        .post-card:hover { transform: translateY(-3px); }
    </style>
</head>
<body>
<nav class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="{{ url('/') }}"><img src="{{ asset('img/logo-white.svg') }}" alt="5050 Logistics Limited"></a>
        <div class="d-flex gap-2">
            <a href="{{ route('quote.create') }}" class="btn btn-sm" style="background: #f59e0b; color: #111827; font-weight: 600;">Get a quote</a>
            <a href="{{ url('/') }}" class="btn btn-sm btn-outline-light">← Home</a>
        </div>
    </div>
</nav>

<div class="container py-5" style="max-width: 64rem;">
    <h1 class="h3 fw-bold mb-1">Logistics insights &amp; updates</h1>
    <p class="text-secondary mb-4">News, tips and company updates from 5050 Logistics.</p>

    <div class="row g-4">
        @forelse ($posts as $post)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-reset">
                    <div class="card post-card h-100">
                        <div class="card-body p-4">
                            <div class="text-secondary small mb-2">{{ $post->published_at?->format('M j, Y') }} · {{ $post->user->name }}</div>
                            <h2 class="h5 fw-bold">{{ $post->title }}</h2>
                            <p class="text-secondary mb-0">{{ $post->excerpt ?? Str::limit(strip_tags($post->body), 120) }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12"><p class="text-secondary">No posts published yet — check back soon.</p></div>
        @endforelse
    </div>

    @if ($posts->hasPages())
        <div class="mt-4">{{ $posts->links() }}</div>
    @endif
</div>
</body>
</html>
