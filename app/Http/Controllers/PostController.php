<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Languages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Aktualności i poradniki. Czytać może każdy (w swoim języku), dodawać i zmieniać – tylko admin.
 */
class PostController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->validate(['kategoria' => ['nullable', Rule::in(array_keys(Post::CATEGORIES))]])['kategoria'] ?? null;

        $posts = Post::published()->forLocale()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->latest('published_at')->paginate(12)->withQueryString();

        return view('posts.index', ['posts' => $posts, 'category' => $category]);
    }

    public function show(Request $request, Post $post): View
    {
        if (! $post->published_at || $post->published_at->isFuture()) {
            abort_unless($request->user()?->isAdmin(), 404);
        }

        return view('posts.show', [
            'post' => $post,
            'more' => Post::published()->forLocale($post->locale)->where('id', '!=', $post->id)->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function admin(): View
    {
        return view('posts.admin', ['posts' => Post::latest()->paginate(30)]);
    }

    public function create(): View
    {
        return view('posts.form', ['post' => new Post(['locale' => app()->getLocale(), 'category' => 'pl'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = new Post($this->validated($request));
        $post->user_id = $request->user()->id;
        $this->applyPublishAndImage($request, $post);

        return redirect()->route('posts.show', $post)->with('status', __('Wpis zapisany.'));
    }

    public function edit(Post $post): View
    {
        return view('posts.form', ['post' => $post]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $post->fill($this->validated($request));
        $this->applyPublishAndImage($request, $post);

        return redirect()->route('posts.show', $post)->with('status', __('Zmiany zapisane.'));
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->image) {
            File::delete(public_path($post->image));
        }
        $post->delete();

        return redirect()->route('admin.posts.admin')->with('status', __('Wpis usunięty.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(array_keys(Post::CATEGORIES))],
            'locale' => ['required', Rule::in(array_keys(Languages::ALL))],
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', 'min:50', 'max:50000'],
            'source_url' => ['nullable', 'url:https,http', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'publish' => ['nullable', 'boolean'],
        ]);
    }

    private function applyPublishAndImage(Request $request, Post $post): void
    {
        if ($request->boolean('publish')) {
            $post->published_at ??= now();
        } else {
            $post->published_at = null;
        }
        if ($request->hasFile('image')) {
            if ($post->image) {
                File::delete(public_path($post->image));
            }
            $name = Str::random(24).'.'.$request->file('image')->guessExtension();
            $request->file('image')->move(public_path('uploads/wpisy'), $name);
            $post->image = 'uploads/wpisy/'.$name;
        }
        $post->save();
    }
}
