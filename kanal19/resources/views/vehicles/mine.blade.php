<x-layouts.app :title="__('Moje pojazdy')">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Moje pojazdy') }}</h1>
            <a href="{{ route('vehicles.create') }}" class="k19-btn-red">{{ __('Sprzedaj pojazd') }}</a>
        </div>
        <div class="mt-6 flex flex-col gap-3">
            @forelse ($listings as $listing)
                <article class="k19-card flex flex-wrap items-center justify-between gap-4 p-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="size-20 shrink-0 overflow-hidden rounded-lg bg-[#E9E7E1]">
                            @if ($c = $listing->cover())<img src="{{ $c->url() }}" alt="" class="h-full w-full object-cover">@endif
                        </div>
                        <div class="min-w-0">
                            <h2 class="font-display text-lg font-bold">{{ $listing->title() }}</h2>
                            <p class="text-sm text-k19-muted">{{ $listing->priceLabel() }} ·
                                {{ ! $listing->isPublished() ? __('Szkic') : ($listing->isActive() ? __('Aktywne').' – '.__('ważna do').' '.$listing->expires_at->format('d.m.Y') : __('Wygasło')) }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route($listing->isPublished() ? 'vehicles.show' : 'vehicles.preview', $listing) }}" class="k19-btn-ghost min-h-11 text-base">{{ __('Zobacz') }}</a>
                        <a href="{{ route('vehicles.edit', $listing) }}" class="k19-btn-dark min-h-11 text-base">{{ __('Edytuj') }}</a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted">{{ __('Nie masz jeszcze ogłoszeń.') }}</div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
