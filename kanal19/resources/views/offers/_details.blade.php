<article class="k19-card flex flex-col gap-5 p-6 sm:p-8">
    <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-md bg-k19-ink px-2 py-1 font-display text-xs font-extrabold uppercase tracking-wide text-white">{{ __($offer->categoryLabel()) }}</span>
        @if ($offer->isPublished())
            <span class="text-sm text-[#5A5F6B]">{{ __('Dodano') }} {{ $offer->published_at->format('d.m.Y') }} · {{ __('ważna do') }} {{ $offer->expires_at->format('d.m.Y') }}</span>
        @endif
    </div>
    <h1 class="font-display text-[clamp(28px,4vw,40px)] font-extrabold leading-tight">{{ $offer->title }}</h1>
    <div class="flex flex-wrap gap-x-6 gap-y-2 text-k19-muted">
        <span>@if ($offer->company)<a href="{{ route('companies.show', $offer->company) }}" class="font-semibold text-k19-ink no-underline hover:text-k19-red">{{ $offer->company->displayName() }}</a>@php($rs = \App\Models\CompanyReview::summaryFor($offer->company->id))@if ($rs) <span class="text-[#8A5A00]" title="{{ __('Opinie kierowców') }}">★ {{ number_format($rs['avg'], 1, ',', '') }} ({{ $rs['count'] }})</span>@endif @endif</span>
        <span>{{ $offer->city }}, {{ $offer->country }}</span>
    </div>
    <div class="flex flex-wrap items-baseline gap-2">
        <span class="font-display text-3xl font-extrabold">{{ $offer->salaryLabel() }}</span>
        <span class="text-k19-muted">{{ __($offer->salary_type) }} / {{ __('mies.') }}</span>
    </div>
    <div class="flex flex-wrap gap-2">
        <span class="k19-tag">{{ __($offer->contractLabel()) }}</span>
        @foreach ($offer->work_modes ?? [] as $mode)
            <span class="k19-tag">{{ __(\App\Models\JobOffer::WORK_MODES[$mode] ?? $mode) }}</span>
        @endforeach
        @if ($offer->no_cv)<span class="k19-tag">{{ __('Aplikuj bez CV') }}</span>@endif
    </div>
    <div>
        <h2 class="font-display text-xl font-bold">{{ __('Opis') }}</h2>
        <p class="mt-2 whitespace-pre-line leading-relaxed">{{ $offer->description }}</p>
    </div>
    @if ($offer->requirements)
        <div>
            <h2 class="font-display text-xl font-bold">{{ __('Wymagania') }}</h2>
            <p class="mt-2 whitespace-pre-line leading-relaxed">{{ $offer->requirements }}</p>
        </div>
    @endif
</article>
