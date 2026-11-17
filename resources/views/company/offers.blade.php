<x-layouts.app :title="__('Moje ogłoszenia')">
    <div class="mx-auto max-w-5xl px-4 pt-10 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Moje ogłoszenia') }}</h1>
                <p class="text-k19-muted">{{ __('Ogłoszenie jest widoczne 30 dni. Po publikacji nie można go edytować.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('company.drivers') }}" class="k19-btn-ghost">{{ __('Baza kierowców') }}</a>
                <a href="{{ route('company.offers.create') }}" class="k19-btn-red">{{ __('Dodaj ogłoszenie za darmo') }}</a>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-3">
            @forelse ($offers as $offer)
                @php
                    $state = ! $offer->isPublished() ? ['Szkic', 'bg-[#FFF1D6] text-[#8A5A00]']
                        : ($offer->isActive() ? ['Aktywne', 'bg-[#DDF3E6] text-[#1E7A46]'] : ['Wygasło', 'bg-[#ECEBE7] text-k19-muted']);
                @endphp
                <article class="k19-card flex flex-wrap items-center justify-between gap-4 p-5">
                    <div class="min-w-0 flex-[1_1_320px]">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md px-2 py-0.5 text-sm font-bold {{ $state[1] }}">{{ __($state[0]) }}</span>
                            <span class="text-sm text-k19-muted">{{ __($offer->categoryLabel()) }} · {{ $offer->city }}</span>
                        </div>
                        <h2 class="mt-1 font-display text-xl font-bold">{{ $offer->title }}</h2>
                        @if ($offer->isPublished())
                            <p class="text-sm text-k19-muted">{{ __('ważna do') }} {{ $offer->expires_at->format('d.m.Y') }} · {{ __('Kandydaci:') }} {{ $offer->applications_count }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if (! $offer->isPublished())
                            <a href="{{ route('company.offers.edit', $offer) }}" class="k19-btn-ghost min-h-11 text-base">{{ __('Edytuj szkic') }}</a>
                            <a href="{{ route('company.offers.preview', $offer) }}" class="k19-btn-dark min-h-11 text-base">{{ __('Podgląd i publikacja') }}</a>
                        @else
                            <a href="{{ route('offers.show', $offer) }}" class="k19-btn-ghost min-h-11 text-base">{{ __('Zobacz') }}</a>
                            <a href="{{ route('company.offers.applications', $offer) }}" class="k19-btn-dark min-h-11 text-base">{{ __('Kandydaci') }} ({{ $offer->applications_count }})</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted">{{ __('Nie masz jeszcze ogłoszeń.') }}</div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
