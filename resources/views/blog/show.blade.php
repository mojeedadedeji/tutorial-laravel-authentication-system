<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $post->title }} — 5050 Logistics Limited</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1f2937; }
        .topbar { background: #0b1f3a; padding: 1rem 0; }
        .topbar img { height: 2.25rem; }
        .article { background: #fff; border-radius: 1rem; box-shadow: 0 4px 24px rgb(0 0 0 / 0.08); }
        .article-body { line-height: 1.8; white-space: pre-line; }
    </style>
</head>
<body>
<nav class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="{{ url('/') }}"><img src="{{ asset('img/logo-white.svg') }}" alt="5050 Logistics Limited"></a>
        <a href="{{ route('blog.index') }}" class="btn btn-sm btn-outline-light">← All posts</a>
    </div>
</nav>

<div class="container py-5" style="max-width: 46rem;">
    <article class="article p-4 p-md-5">
        <div class="text-secondary small mb-2">{{ $post->published_at?->format('F j, Y') }} · {{ $post->user->name }}</div>
        <h1 class="h3 fw-bold mb-4">{{ $post->title }}</h1>
        <div class="article-body">{{ $post->body }}</div>
    </article>
</div>
</body>
</html>
