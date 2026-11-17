<x-layouts.app :title="$post->title">
    <article class="mx-auto max-w-3xl px-4 pt-10 sm:px-6">
        <a href="{{ route('posts.index', ['kategoria' => $post->category]) }}" class="font-semibold text-k19-red">← {{ __(\App\Models\Post::CATEGORIES[$post->category] ?? '') }}</a>
        @unless ($post->published_at)<p class="mt-3 rounded-xl bg-[#FFF1D6] p-3 font-semibold text-[#8A5A00]">{{ __('Szkic – widoczny tylko dla administratora.') }}</p>@endunless
        <h1 class="mt-3 font-display text-[clamp(30px,4.5vw,48px)] font-extrabold leading-tight">{{ $post->title }}</h1>
        <p class="mt-2 text-sm text-k19-muted">{{ $post->published_at?->format('d.m.Y') }}</p>
        @if ($post->imageUrl())<img src="{{ $post->imageUrl() }}" alt="" class="mt-6 w-full rounded-2xl object-cover">@endif
        @if ($post->excerpt)<p class="mt-6 text-xl font-semibold leading-relaxed">{{ $post->excerpt }}</p>@endif
        <div class="mt-6 whitespace-pre-line text-lg leading-relaxed">{{ $post->body }}</div>
        @if ($post->source_url)
            <p class="mt-6 text-sm text-k19-muted">{{ __('Źródło:') }} <a href="{{ $post->source_url }}" target="_blank" rel="noopener nofollow" class="text-k19-red underline">{{ parse_url($post->source_url, PHP_URL_HOST) }}</a></p>
        @endif
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('admin.posts.edit', $post) }}" class="k19-btn-ghost mt-6">{{ __('Edytuj') }}</a>
        @endif
    </article>

    @if ($more->isNotEmpty())
        <section class="mx-auto mt-14 max-w-6xl px-4 sm:px-6">
            <h2 class="font-display text-2xl font-extrabold">{{ __('Czytaj także') }}</h2>
            <div class="mt-4 grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-4">
                @foreach ($more as $p)<x-post-card :post="$p" />@endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
