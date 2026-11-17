@props(['ad', 'compact' => false])
{{-- Reklama partnera w języku strony. Oznaczona jako reklama, link z rel="sponsored". --}}
<aside aria-label="{{ __('Reklama') }}" class="k19-card relative flex {{ $compact ? 'flex-col' : 'flex-wrap items-center' }} gap-4 overflow-hidden p-4">
    <span class="absolute right-3 top-2 text-[11px] font-bold uppercase tracking-widest text-k19-muted">{{ __('Reklama') }}</span>
    @if ($ad->imageUrl())
        <img src="{{ $ad->imageUrl() }}" alt="" loading="lazy" class="{{ $compact ? 'aspect-[16/9] w-full' : 'h-28 w-44' }} shrink-0 rounded-xl object-cover">
    @endif
    <div class="flex min-w-0 flex-1 flex-col gap-1.5">
        <p class="font-display text-xl font-extrabold leading-tight">{{ $ad->headline }}</p>
        <p class="text-[15px] text-k19-muted">{{ $ad->text }}</p>
        <div class="mt-1 flex flex-wrap items-center gap-3">
            <a href="{{ $ad->campaign->target_url }}" target="_blank" rel="sponsored nofollow noopener" class="k19-btn-red min-h-10 px-4 text-base">{{ __('Sprawdź') }}</a>
            <a href="{{ route('article', $ad) }}" class="font-semibold text-k19-ink underline">{{ __('Czytaj artykuł') }}</a>
        </div>
    </div>
</aside>
