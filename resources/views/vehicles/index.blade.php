@php($v = \App\Models\VehicleListing::class)
<x-layouts.app :title="__('Giełda pojazdów')">
    <section class="bg-k19-ink pb-12 pt-10 text-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-end justify-between gap-6 px-4 sm:px-6">
            <div class="max-w-2xl">
                <h1 class="font-display text-[clamp(34px,5vw,56px)] font-extrabold leading-none">{{ __('Giełda pojazdów') }}</h1>
                <p class="mt-3 text-lg text-[#C9CCD3]">{{ __('Ciągniki, naczepy, ciężarówki, busy i autobusy – nowe, używane i poleasingowe. Wystaw pojazd za darmo.') }}</p>
            </div>
            <a href="{{ route('vehicles.create') }}" class="k19-btn-red">{{ __('Sprzedaj pojazd') }}</a>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6">
        <nav class="flex flex-wrap gap-2" aria-label="{{ __('Stan pojazdu') }}">
            <a href="{{ route('vehicles.index', array_filter(['typ' => $filters['typ'] ?? null])) }}" class="k19-pill {{ empty($filters['stan']) ? 'border-k19-red bg-k19-red text-white' : '' }}">{{ __('Wszystkie') }}</a>
            @foreach ($v::CONDITIONS as $k => $l)
                <a href="{{ route('vehicles.index', array_filter(['typ' => $filters['typ'] ?? null, 'stan' => $k])) }}" class="k19-pill {{ ($filters['stan'] ?? '') === $k ? 'border-k19-red bg-k19-red text-white' : '' }}">{{ __($l) }}</a>
            @endforeach
        </nav>
    </div>

    <section class="mx-auto flex max-w-6xl flex-wrap items-start gap-8 px-4 pt-6 sm:px-6">
        <form method="GET" class="k19-card flex flex-[1_1_240px] flex-col gap-4 p-5" aria-label="{{ __('Filtry') }}">
            <input type="hidden" name="stan" value="{{ $filters['stan'] ?? '' }}">
            <div>
                <label for="typ" class="k19-label">{{ __('Rodzaj pojazdu') }}</label>
                <select id="typ" name="typ" class="k19-input">
                    <option value="">{{ __('Wszystkie') }}</option>
                    @foreach ($v::TYPES as $k => $l)<option value="{{ $k }}" @selected(($filters['typ'] ?? '') === $k)>{{ __($l) }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="marka" class="k19-label">{{ __('Marka') }}</label>
                <input id="marka" name="marka" value="{{ $filters['marka'] ?? '' }}" placeholder="{{ __('np. Volvo, Scania, DAF') }}" class="k19-input">
            </div>
            <div>
                <label for="cena_do" class="k19-label">{{ __('Cena do') }}</label>
                <input id="cena_do" name="cena_do" type="number" min="0" value="{{ $filters['cena_do'] ?? '' }}" class="k19-input">
            </div>
            <div>
                <label for="rok_od" class="k19-label">{{ __('Rocznik od') }}</label>
                <input id="rok_od" name="rok_od" type="number" min="1950" max="2100" value="{{ $filters['rok_od'] ?? '' }}" class="k19-input">
            </div>
            <div>
                <label for="sort" class="k19-label">{{ __('Sortuj') }}</label>
                <select id="sort" name="sort" class="k19-input">
                    <option value="new" @selected(($filters['sort'] ?? 'new') === 'new')>{{ __('Najnowsze') }}</option>
                    <option value="cheap" @selected(($filters['sort'] ?? '') === 'cheap')>{{ __('Najtańsze') }}</option>
                    <option value="expensive" @selected(($filters['sort'] ?? '') === 'expensive')>{{ __('Najdroższe') }}</option>
                </select>
            </div>
            <button class="k19-btn-red">{{ __('Pokaż pojazdy') }}</button>
        </form>

        <div class="flex min-w-0 flex-[999_1_560px] flex-col gap-4">
            <p class="text-k19-muted">{{ __('Znaleziono:') }} {{ $listings->total() }}</p>
            @if ($listings->isEmpty())
                <div class="rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted">
                    {{ __('Brak ogłoszeń. Bądź pierwszy – wystaw swój pojazd za darmo.') }}
                    <div class="mt-4"><a href="{{ route('vehicles.create') }}" class="k19-btn-dark">{{ __('Sprzedaj pojazd') }}</a></div>
                </div>
            @else
                <div class="grid grid-cols-[repeat(auto-fill,minmax(240px,1fr))] gap-4">
                    @foreach ($listings as $listing)<x-vehicle-card :listing="$listing" />@endforeach
                </div>
            @endif
            {{ $listings->links() }}
        </div>
    </section>
</x-layouts.app>
