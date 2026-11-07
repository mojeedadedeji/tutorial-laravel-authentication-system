<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Public blog index.
     */
    public function index(): View
    {
        return view('blog.index', [
            'posts' => Post::published()->paginate(9),
        ]);
    }

    /**
     * Public single post.
     */
    public function show(string $slug): View
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return view('blog.show', ['post' => $post]);
    }

    /**
     * Admin: list all posts.
     */
    public function adminIndex(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::with('user')->latest()->paginate(20),
        ]);
    }

    /**
     * Admin: create form.
     */
    public function create(): View
    {
        return view('admin.posts.form', ['post' => new Post()]);
    }

    /**
     * Admin: store post.
     */
    public function store(Request $request): RedirectResponse
    {
        $post = Post::create($this->validated($request) + [
            'user_id' => $request->user()->id,
            'slug' => Post::slugify($request->input('title')),
            'published_at' => $request->boolean('is_published') ? now() : null,
        ]);

        return redirect()->route('admin.posts.index')->with('status', 'Post created.');
    }

    /**
     * Admin: edit form.
     */
    public function edit(Post $post): View
    {
        return view('admin.posts.form', ['post' => $post]);
    }

    /**
     * Admin: update post.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Post::slugify($data['title']);
        $data['published_at'] = $request->boolean('is_published')
            ? ($post->published_at ?? now())
            : null;

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('status', 'Post updated.');
    }

    /**
     * Admin: delete post.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return back()->with('status', 'Post deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }
}
