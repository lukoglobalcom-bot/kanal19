<x-layouts.app>
    {{-- Hero z wyszukiwarką --}}
    <section class="bg-k19-ink pb-16 pt-12 text-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-10 px-4 sm:px-6">
            <div class="flex min-w-0 flex-[999_1_520px] flex-col gap-6">
                <p class="text-sm font-bold uppercase tracking-[0.12em] text-[#C9CCD3]">{{ __('Portal pracy dla kierowców zawodowych') }}</p>
                <h1 class="font-display text-[clamp(40px,6vw,72px)] font-extrabold leading-[0.95]">
                    {{ __('Praca za kierownicą.') }}<br><span class="text-k19-red-light">{{ __('W każdej kategorii.') }}</span>
                </h1>
                <p class="max-w-2xl text-lg leading-relaxed text-[#C9CCD3]">{{ __('Oferty dla kierowców kat. B, C, C+E i D, taksówkarzy, kurierów, kierowców ADR i budowlanych – w kraju i za granicą. Aplikuj w kilka sekund, także bez CV.') }}</p>
            </div>
            <figure class="m-0 flex-[1_1_300px] rounded-3xl border-4 border-k19-red bg-k19-paper p-3">
                <img src="{{ asset('images/kanal19-logo.png') }}" alt="{{ __('Logo kanal19.pl – portal dla kierowców') }}" class="block h-auto w-full rounded-2xl">
            </figure>

            <form role="search" method="GET" action="{{ route('home') }}#oferty" class="flex w-full flex-wrap items-end gap-3 rounded-2xl bg-white p-4 text-k19-ink">
                <div class="flex-[2_1_240px]">
                    <label for="q" class="k19-label text-k19-muted">{{ __('Stanowisko lub słowo kluczowe') }}</label>
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="k19-input" placeholder="{{ __('np. kierowca C+E chłodnia') }}">
                </div>
                <div class="flex-[1_1_180px]">
                    <label for="miasto" class="k19-label text-k19-muted">{{ __('Lokalizacja') }}</label>
                    <input id="miasto" name="miasto" value="{{ $filters['miasto'] ?? '' }}" class="k19-input" placeholder="{{ __('Miasto lub województwo') }}">
                </div>
                <div class="flex-[1_1_180px]">
                    <label for="kategoria" class="k19-label text-k19-muted">{{ __('Kategoria') }}</label>
                    <select id="kategoria" name="kategoria" class="k19-input">
                        <option value="">{{ __('Wszystkie kategorie') }}</option>
                        @foreach (\App\Models\JobOffer::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['kategoria'] ?? '') === $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="k19-btn-dark">{{ __('Szukaj ofert') }}</button>
            </form>
            <div class="flex w-full flex-wrap items-center gap-3">
                <a href="{{ route('vehicles.create') }}" class="k19-btn bg-white text-k19-red hover:bg-k19-paper">{{ __('Sprzedaj pojazd') }}</a>
                <a href="{{ route('vehicles.index') }}" class="font-semibold text-white underline">{{ __('Giełda pojazdów') }} →</a>
            </div>
        </div>
    </section>

    @if ($billboard)
        <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6"><x-ad-slot :ad="$billboard" /></div>
    @endif

    {{-- Kategorie --}}
    <section id="kategorie" class="mx-auto max-w-6xl px-4 pt-14 sm:px-6">
        <h2 class="font-display text-[clamp(28px,3.4vw,40px)] font-extrabold">{{ __('Wybierz swoją kategorię') }}</h2>
        <div class="mt-6 grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-3">
            @foreach (\App\Models\JobOffer::CATEGORIES as $key => $label)
                <a href="{{ route('home', ['kategoria' => $key]) }}#oferty"
                   class="k19-card flex min-h-24 flex-col justify-between gap-3 p-4 no-underline hover:border-k19-red {{ ($filters['kategoria'] ?? '') === $key ? 'border-2 border-k19-red' : '' }}">
                    <span class="k19-badge h-8 self-start px-2.5 text-base" aria-hidden="true">{{ ['b' => 'B', 'c' => 'C', 'ce' => 'C+E', 'd' => 'D', 'taxi' => 'TAXI', 'kurier' => 'BUS', 'adr' => 'ADR', 'bud' => 'HDS', 'abroad' => 'EU', 'newbie' => 'START'][$key] }}</span>
                    <span class="font-display text-lg font-bold text-k19-ink">{{ __($label) }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Oferty z filtrami --}}
    <section id="oferty" class="mx-auto flex max-w-6xl flex-wrap items-start gap-8 px-4 pt-12 sm:px-6">
        <form method="GET" action="{{ route('home') }}#oferty" aria-label="{{ __('Filtry') }}" class="k19-card flex flex-[1_1_240px] flex-col gap-6 p-5">
            <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">
            <input type="hidden" name="miasto" value="{{ $filters['miasto'] ?? '' }}">
            <input type="hidden" name="kategoria" value="{{ $filters['kategoria'] ?? '' }}">
            <fieldset class="flex flex-col gap-2">
                <legend class="mb-2 font-display text-lg font-bold">{{ __('Rodzaj umowy') }}</legend>
                @foreach (\App\Models\JobOffer::CONTRACTS as $key => $label)
                    <label class="flex min-h-8 items-center gap-2.5"><input type="checkbox" name="umowa[]" value="{{ $key }}" class="size-[18px] accent-k19-red" @checked(in_array($key, $filters['umowa'] ?? []))>{{ __($label) }}</label>
                @endforeach
            </fieldset>
            <fieldset class="flex flex-col gap-2">
                <legend class="mb-2 font-display text-lg font-bold">{{ __('Tryb pracy') }}</legend>
                @foreach (\App\Models\JobOffer::WORK_MODES as $key => $label)
                    <label class="flex min-h-8 items-center gap-2.5"><input type="checkbox" name="tryb[]" value="{{ $key }}" class="size-[18px] accent-k19-red" @checked(in_array($key, $filters['tryb'] ?? []))>{{ __($label) }}</label>
                @endforeach
                <label class="flex min-h-8 items-center gap-2.5"><input type="checkbox" name="bez_cv" value="1" class="size-[18px] accent-k19-red" @checked(!empty($filters['bez_cv']))>{{ __('Aplikuj bez CV') }}</label>
            </fieldset>
            <div>
                <label for="od" class="k19-label font-display text-lg">{{ __('Wynagrodzenie od') }}</label>
                <input id="od" name="od" type="number" min="0" value="{{ $filters['od'] ?? '' }}" class="k19-input" placeholder="{{ __('np. 9 000') }}">
            </div>
            <button class="k19-btn-red">{{ __('Pokaż oferty') }}</button>
            @if ($box)
                <div class="-mx-1"><x-ad-slot :ad="$box" compact /></div>
            @endif
        </form>

        <div class="flex min-w-0 flex-[999_1_560px] flex-col gap-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-[clamp(26px,3vw,34px)] font-extrabold">
                        {{ !empty($filters['kategoria']) ? __(\App\Models\JobOffer::CATEGORIES[$filters['kategoria']]) : __('Wszystkie oferty') }}
                    </h2>
                    <p class="text-k19-muted">{{ __('Liczba ofert:') }} {{ $offers->total() }}</p>
                </div>
                <form method="GET" action="{{ route('home') }}#oferty" class="flex items-center gap-2">
                    @foreach (request()->except('sort', 'page') as $k => $v)
                        @if (is_array($v)) @foreach ($v as $vv)<input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">@endforeach
                        @else <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
                    @endforeach
                    <label for="sort" class="text-sm font-semibold text-k19-muted">{{ __('Sortuj') }}</label>
                    <select id="sort" name="sort" onchange="this.form.submit()" class="k19-input min-h-11 w-auto">
                        <option value="new" @selected(($filters['sort'] ?? 'new') === 'new')>{{ __('Najnowsze') }}</option>
                        <option value="pay" @selected(($filters['sort'] ?? '') === 'pay')>{{ __('Najwyższe wynagrodzenie') }}</option>
                    </select>
                </form>
            </div>

            @forelse ($offers as $offer)
                <x-offer-card :offer="$offer" />
            @empty
                <div class="rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted">{{ __('Brak ofert w tej kategorii.') }}</div>
            @endforelse

            {{ $offers->links() }}
        </div>
    </section>

    {{-- Mapa kierowców i SOS --}}
    <section class="mx-auto max-w-6xl px-4 pt-16 sm:px-6">
        <div class="grid gap-4 md:grid-cols-[2fr_1fr]">
            <a href="{{ route('map.index') }}" class="k19-card flex flex-col gap-2 p-6 text-k19-ink no-underline hover:border-k19-red">
                <span class="font-display text-sm font-extrabold uppercase tracking-widest text-k19-red">{{ __('Mapa kierowców') }}</span>
                <span class="font-display text-[clamp(24px,3vw,32px)] font-extrabold leading-tight">{{ __('Wolne miejsca na parkingach i ceny paliwa od kierowców') }}</span>
                <span class="text-k19-muted">{{ __('Zgłaszaj i sprawdzaj na żywo – w PLN, EUR i walucie każdego kraju.') }}</span>
            </a>
            <a href="{{ route('map.sos') }}" class="flex flex-col justify-center gap-2 rounded-2xl bg-k19-red p-6 text-white no-underline">
                <span class="font-display text-5xl font-extrabold">SOS</span>
                <span class="font-semibold">{{ __('Wezwij pomoc innych kierowców jednym przyciskiem.') }}</span>
            </a>
        </div>
    </section>

    @if ($posts->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pt-16 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 class="font-display text-[clamp(28px,3.4vw,40px)] font-extrabold">{{ __('Aktualności') }}</h2>
                <a href="{{ route('posts.index') }}" class="k19-btn-ghost">{{ __('Zobacz wszystkie') }}</a>
            </div>
            <div class="mt-6 grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-4">
                @foreach ($posts as $post)<x-post-card :post="$post" />@endforeach
            </div>
        </section>
    @endif

    {{-- Giełda pojazdów --}}
    <section class="mx-auto max-w-6xl px-4 pt-16 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-[clamp(28px,3.4vw,40px)] font-extrabold">{{ __('Giełda pojazdów') }}</h2>
                <p class="text-k19-muted">{{ __('Nowe, używane i poleasingowe – ciągniki, naczepy, busy i autobusy.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('vehicles.index') }}" class="k19-btn-ghost">{{ __('Zobacz wszystkie') }}</a>
                <a href="{{ route('vehicles.create') }}" class="k19-btn-red">{{ __('Sprzedaj pojazd') }}</a>
            </div>
        </div>
        @if ($vehicles->isNotEmpty())
            <div class="mt-6 grid grid-cols-[repeat(auto-fill,minmax(240px,1fr))] gap-4">
                @foreach ($vehicles as $listing)<x-vehicle-card :listing="$listing" />@endforeach
            </div>
        @endif
    </section>

    {{-- Dla pracodawców --}}
    <section class="mx-auto max-w-6xl px-4 pt-16 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-6 rounded-3xl bg-k19-red p-8 text-white sm:p-12">
            <div class="max-w-xl">
                <span class="inline-flex rounded-md bg-k19-ink px-2.5 py-1 font-display text-sm font-extrabold uppercase tracking-wider">{{ __('Ogłoszenie za 0 zł') }}</span>
                <h2 class="mt-3 font-display text-[clamp(28px,3.6vw,44px)] font-extrabold leading-tight">{{ __('Szukasz kierowców? Dodaj ogłoszenie za darmo') }}</h2>
                <p class="mt-2 text-lg leading-relaxed">{{ __('Darmowe ogłoszenie w każdej kategorii, widoczne przez 30 dni. Po publikacji nie można go edytować, więc przed dodaniem sprawdzisz je w podglądzie.') }}</p>
            </div>
            <a href="{{ route('register', ['konto' => 'company']) }}" class="k19-btn bg-white text-k19-ink hover:bg-k19-paper">{{ __('Dodaj darmowe ogłoszenie') }}</a>
        </div>
    </section>
</x-layouts.app>
