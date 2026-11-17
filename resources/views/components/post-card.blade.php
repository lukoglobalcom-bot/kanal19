@props(['post'])
<a href="{{ route('posts.show', $post) }}" class="k19-card flex flex-col overflow-hidden text-k19-ink no-underline hover:border-k19-red">
    @if ($post->imageUrl())
        <img src="{{ $post->imageUrl() }}" alt="" loading="lazy" class="aspect-[16/9] w-full object-cover">
    @endif
    <div class="flex flex-1 flex-col gap-2 p-5">
        <span class="self-start rounded-md bg-k19-ink px-2 py-0.5 font-display text-xs font-extrabold uppercase tracking-wide text-white">{{ __(\App\Models\Post::CATEGORIES[$post->category] ?? '') }}</span>
        <h3 class="font-display text-xl font-bold leading-snug">{{ $post->title }}</h3>
        @if ($post->excerpt)<p class="text-[15px] text-k19-muted">{{ $post->excerpt }}</p>@endif
        <p class="mt-auto pt-2 text-sm text-k19-muted">{{ $post->published_at?->format('d.m.Y') }}</p>
    </div>
</a>
