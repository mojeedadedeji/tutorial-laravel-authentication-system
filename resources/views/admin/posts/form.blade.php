@extends('layouts.app')

@section('title', ($post->exists ? 'Edit' : 'New') . ' post - Admin - ' . config('app.name'))

@section('content')
    <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0 fw-bold">{{ $post->exists ? 'Edit post' : 'New post' }}</h1>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm">← Posts</a>
    </div>

    <div class="card" style="max-width: 44rem; border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.08), 0 4px 12px rgb(0 0 0 / 0.05);">
        <div class="card-body p-4">
            <form method="POST" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}">
                @csrf
                @if ($post->exists) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="2" maxlength="500">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Body</label>
                    <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="12" required>{{ old('body', $post->body) }}</textarea>
                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Plain text or simple HTML. Line breaks are preserved.</div>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" @checked(old('is_published', $post->is_published))>
                    <label class="form-check-label" for="is_published">Publish immediately</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">{{ $post->exists ? 'Save changes' : 'Create post' }}</button>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
