<x-layouts.app :title="$creative->article_title">
    <article class="mx-auto max-w-3xl px-4 pt-10 sm:px-6">
        <p class="text-xs font-bold uppercase tracking-widest text-k19-muted">{{ __('Artykuł sponsorowany') }} · {{ $campaign->advertiser?->advertiserProfile?->brand }}</p>
        <h1 class="mt-2 font-display text-[clamp(30px,4.5vw,48px)] font-extrabold leading-tight">{{ $creative->article_title }}</h1>
        @if ($creative->imageUrl())
            <img src="{{ $creative->imageUrl() }}" alt="" class="mt-6 w-full rounded-2xl object-cover">
        @endif
        <div class="mt-6 whitespace-pre-line text-lg leading-relaxed">{{ $creative->article_body }}</div>
        <a href="{{ $campaign->target_url }}" target="_blank" rel="sponsored nofollow noopener" class="k19-btn-red mt-8">{{ __('Przejdź do strony partnera') }}</a>
    </article>
</x-layouts.app>
