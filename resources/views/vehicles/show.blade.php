@php($v = \App\Models\VehicleListing::class)
@php($preview = $preview ?? false)
<x-layouts.app :title="$listing->title()">
    <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
        @if ($preview)
            <div class="mb-5 rounded-2xl border-2 border-dashed border-k19-red bg-white p-4 font-semibold">{{ __('To jest podgląd. Tak kupujący zobaczą Twoje ogłoszenie.') }}</div>
        @endif
        <a href="{{ route('vehicles.index') }}" class="font-semibold text-k19-red">← {{ __('Giełda pojazdów') }}</a>

        <div class="mt-4 flex flex-wrap items-start gap-8">
            <div class="flex min-w-0 flex-[999_1_560px] flex-col gap-3">
                @if ($listing->photos->isNotEmpty())
                    <img id="k19-main" src="{{ $listing->photos->first()->url() }}" alt="{{ $listing->title() }}" class="aspect-[4/3] w-full rounded-2xl bg-[#E9E7E1] object-cover">
                    @if ($listing->photos->count() > 1)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($listing->photos as $photo)
                                <button type="button" onclick="document.getElementById('k19-main').src='{{ $photo->url() }}'" class="size-20 overflow-hidden rounded-lg border border-k19-line" aria-label="{{ __('Pokaż zdjęcie') }} {{ $loop->iteration }}">
                                    <img src="{{ $photo->url() }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="flex aspect-[4/3] items-center justify-center rounded-2xl bg-[#E9E7E1] font-display text-xl font-bold text-k19-muted">{{ __('Brak zdjęcia') }}</div>
                @endif

                <section class="k19-card mt-3 p-6">
                    <h2 class="font-display text-xl font-bold">{{ __('Opis') }}</h2>
                    <p class="mt-2 whitespace-pre-line leading-relaxed">{{ $listing->description }}</p>
                </section>
            </div>

            <aside class="flex flex-[1_1_300px] flex-col gap-4">
                <div class="k19-card flex flex-col gap-3 p-6">
                    <span class="self-start rounded-md px-2 py-0.5 font-display text-xs font-extrabold uppercase tracking-wide text-white {{ $listing->condition === 'new' ? 'bg-k19-red' : 'bg-k19-ink' }}">{{ __($v::CONDITIONS[$listing->condition] ?? '') }}</span>
                    <h1 class="font-display text-3xl font-extrabold leading-tight">{{ $listing->title() }}</h1>
                    <p class="font-display text-3xl font-extrabold">{{ $listing->priceLabel() }} <span class="text-base font-semibold text-k19-muted">{{ __($listing->price_type) }}</span></p>
                    @if ($listing->vat_invoice)<span class="k19-tag self-start">{{ __('Faktura VAT') }}</span>@endif
                    <p class="text-k19-muted">{{ $listing->city }}, {{ $listing->country }}</p>
                    <details class="rounded-xl bg-k19-paper p-3">
                        <summary class="cursor-pointer font-display text-lg font-bold">{{ __('Pokaż telefon') }}</summary>
                        <a href="tel:{{ preg_replace('/\s+/', '', $listing->phone) }}" class="mt-2 block font-display text-2xl font-extrabold text-k19-red">{{ $listing->phone }}</a>
                    </details>
                </div>

                <dl class="k19-card grid grid-cols-2 gap-x-4 gap-y-2 p-6 text-[15px]">
                    <dt class="text-k19-muted">{{ __('Rodzaj pojazdu') }}</dt><dd class="font-semibold">{{ __($v::TYPES[$listing->type] ?? '') }}</dd>
                    @if ($listing->year)<dt class="text-k19-muted">{{ __('Rocznik') }}</dt><dd class="font-semibold">{{ $listing->year }}</dd>@endif
                    @if ($listing->mileage !== null)<dt class="text-k19-muted">{{ __('Przebieg') }}</dt><dd class="font-semibold">{{ number_format($listing->mileage, 0, ',', ' ') }} km</dd>@endif
                    @if ($listing->power_hp)<dt class="text-k19-muted">{{ __('Moc') }}</dt><dd class="font-semibold">{{ $listing->power_hp }} KM</dd>@endif
                    @if ($listing->fuel)<dt class="text-k19-muted">{{ __('Paliwo') }}</dt><dd class="font-semibold">{{ __($v::FUELS[$listing->fuel] ?? '') }}</dd>@endif
                    @if ($listing->gearbox)<dt class="text-k19-muted">{{ __('Skrzynia biegów') }}</dt><dd class="font-semibold">{{ __($v::GEARBOXES[$listing->gearbox] ?? '') }}</dd>@endif
                    @if ($listing->euro)<dt class="text-k19-muted">{{ __('Norma emisji') }}</dt><dd class="font-semibold">{{ strtoupper($listing->euro) }}</dd>@endif
                </dl>

                @can('update', $listing)
                    <div class="k19-card flex flex-col gap-3 p-6">
                        @if (! $listing->isPublished())
                            <form method="POST" action="{{ route('vehicles.publish', $listing) }}">
                                @csrf
                                <button class="k19-btn-red w-full">{{ __('Publikuj ogłoszenie') }}</button>
                            </form>
                        @elseif ($listing->isActive())
                            <p class="text-sm text-k19-muted">{{ __('ważna do') }} {{ $listing->expires_at->format('d.m.Y') }}</p>
                        @else
                            <form method="POST" action="{{ route('vehicles.publish', $listing) }}">
                                @csrf
                                <button class="k19-btn-red w-full">{{ __('Odnów na 30 dni') }}</button>
                            </form>
                        @endif
                        <a href="{{ route('vehicles.edit', $listing) }}" class="k19-btn-ghost">{{ __('Edytuj') }}</a>
                        <form method="POST" action="{{ route('vehicles.destroy', $listing) }}" onsubmit="return confirm(@js(__('Usunąć to ogłoszenie?')))">
                            @csrf @method('DELETE')
                            <button class="w-full text-center font-semibold text-k19-red underline">{{ __('Usuń') }}</button>
                        </form>
                    </div>
                @endcan
            </aside>
        </div>
    </div>
</x-layouts.app>
