<x-layouts.app :title="$post->exists ? __('Edytuj wpis') : __('Nowy wpis')">
    <div class="mx-auto max-w-3xl px-4 pt-10 sm:px-6">
        <a href="{{ route('admin.posts.admin') }}" class="font-semibold text-k19-red">← {{ __('Aktualności') }}</a>
        <h1 class="mt-2 font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ $post->exists ? __('Edytuj wpis') : __('Nowy wpis') }}</h1>
        <form method="POST" enctype="multipart/form-data" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" class="k19-card mt-6 flex flex-col gap-5 p-6 sm:p-8">
            @csrf
            @if ($post->exists) @method('PUT') @endif
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="category" class="k19-label">{{ __('Kategoria') }}</label>
                    <select id="category" name="category" class="k19-input">
                        @foreach (\App\Models\Post::CATEGORIES as $k => $l)<option value="{{ $k }}" @selected(old('category', $post->category) === $k)>{{ __($l) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="locale" class="k19-label">{{ __('Język wpisu') }}</label>
                    <select id="locale" name="locale" class="k19-input">
                        @foreach (\App\Support\Languages::ALL as $code => $name)<option value="{{ $code }}" @selected(old('locale', $post->locale) === $code)>{{ $name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="title" class="k19-label">{{ __('Tytuł') }}</label>
                <input id="title" name="title" maxlength="180" required value="{{ old('title', $post->title) }}" class="k19-input">
                @error('title')<p class="k19-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="excerpt" class="k19-label">{{ __('Zajawka (opcjonalnie)') }}</label>
                <textarea id="excerpt" name="excerpt" maxlength="300" rows="2" class="k19-input py-3">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>
            <div>
                <label for="body" class="k19-label">{{ __('Treść') }}</label>
                <textarea id="body" name="body" rows="14" required class="k19-input py-3">{{ old('body', $post->body) }}</textarea>
                @error('body')<p class="k19-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="source_url" class="k19-label">{{ __('Źródło (link, opcjonalnie)') }}</label>
                    <input id="source_url" name="source_url" type="url" value="{{ old('source_url', $post->source_url) }}" class="k19-input" placeholder="https://">
                    @error('source_url')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="image" class="k19-label">{{ __('Zdjęcie (opcjonalnie)') }}</label>
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="k19-input py-3">
                    @error('image')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <label class="flex items-center gap-2.5"><input type="checkbox" name="publish" value="1" class="size-[18px] accent-k19-red" @checked(old('publish', $post->published_at !== null || ! $post->exists))>{{ __('Opublikuj od razu') }}</label>
            <button class="k19-btn-red self-start">{{ __('Zapisz') }}</button>
        </form>
    </div>
</x-layouts.app>
