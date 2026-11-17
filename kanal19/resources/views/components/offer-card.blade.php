@props(['offer'])
<article class="k19-card flex flex-wrap items-center justify-between gap-5 p-5">
    <div class="flex min-w-0 flex-[1_1_340px] flex-col gap-2.5">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-md bg-k19-ink px-2 py-1 font-display text-xs font-extrabold uppercase tracking-wide text-white">{{ __($offer->categoryLabel()) }}</span>
            <span class="text-sm text-[#5A5F6B]">{{ $offer->published_at?->diffForHumans() }}</span>
        </div>
        <h3 class="font-display text-xl font-bold leading-snug">
            <a href="{{ route('offers.show', $offer) }}" class="text-k19-ink no-underline hover:text-k19-red">{{ $offer->title }}</a>
        </h3>
        <div class="flex flex-wrap gap-4 text-[15px] text-k19-muted">
            <span>{{ $offer->company?->displayName() }}</span>
            <span>{{ $offer->city }}, {{ $offer->country }}</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="k19-tag">{{ __($offer->contractLabel()) }}</span>
            @foreach ($offer->work_modes ?? [] as $mode)
                <span class="k19-tag">{{ __(\App\Models\JobOffer::WORK_MODES[$mode] ?? $mode) }}</span>
            @endforeach
            @if ($offer->no_cv)<span class="k19-tag">{{ __('Aplikuj bez CV') }}</span>@endif
        </div>
    </div>
    <div class="flex flex-[0_1_220px] flex-col items-start gap-2">
        <span class="font-display text-2xl font-extrabold">{{ $offer->salaryLabel() }}</span>
        <span class="-mt-1 text-sm text-[#5A5F6B]">{{ __($offer->salary_type) }} / {{ __('mies.') }}</span>
        <a href="{{ route('offers.show', $offer) }}" class="k19-btn-dark min-h-11 text-base">{{ __('Zobacz i aplikuj') }}</a>
    </div>
</article>
