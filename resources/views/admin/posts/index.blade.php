@extends('layouts.app')

@section('title', 'Blog posts - Admin - ' . config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Blog posts</h1>
            <p class="text-secondary mb-0">{{ $posts->total() }} posts</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">+ New post</a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Admin</a>
        </div>
    </div>

    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="card" style="border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Title</th><th>Author</th><th>Status</th><th>Published</th><th></th></tr></thead>
                <tbody>
                    @forelse ($posts as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->title }}</td>
                            <td>{{ $p->user->name }}</td>
                            <td>{!! $p->is_published ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' !!}</td>
                            <td>{{ $p->published_at?->format('M j, Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.posts.edit', $p) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.posts.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Delete this post?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-5">No posts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($posts->hasPages())
            <div class="card-body border-top">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection
