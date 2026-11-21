@php($canReport = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasVerifiedPhone()))
<x-layouts.app title="SOS">
    <div class="mx-auto max-w-xl px-4 pt-8 sm:px-6">
        <x-banner name="N03-sos" fallback="A10-pomoc-na-trasie" :alt="'SOS'" class="mb-5 max-h-[320px] object-cover" />
        <h1 class="font-display text-5xl font-extrabold">SOS</h1>
        <p class="mt-1 text-k19-muted">{{ __('Wezwij pomoc innych kierowców jednym przyciskiem. Twoja lokalizacja pojawi się na mapie na 6 godzin.') }}</p>

        <a href="tel:112" class="mt-5 flex min-h-20 items-center justify-center gap-3 rounded-2xl bg-k19-red px-4 py-3 text-center font-display text-2xl font-extrabold leading-tight text-white no-underline shadow-lg">
            📞 {{ __('Zagrożenie życia? Dzwoń 112') }}
        </a>

        @if ($canReport)
            <div class="mt-6 grid grid-cols-2 gap-3" id="k19-sos">
                @foreach (\App\Models\MapReport::SOS_PROBLEMS as $key => $label)
                    <button type="button" data-problem="{{ $key }}"
                            class="flex min-h-32 flex-col items-center justify-center gap-2 rounded-2xl border-4 p-3 text-center font-display text-xl font-extrabold leading-tight {{ $key === 'life' ? 'border-k19-red bg-k19-red text-white' : 'border-k19-ink bg-white text-k19-ink' }}">
                        <span class="text-4xl" aria-hidden="true">{{ ['vehicle' => '🚛', 'trailer' => '🔗', 'health' => '🩺', 'life' => '⚠️'][$key] }}</span>
                        {{ __($label) }}
                    </button>
                @endforeach
            </div>
            <label for="k19-sos-msg" class="k19-label mt-4">{{ __('Krótki opis (opcjonalnie)') }}</label>
            <textarea id="k19-sos-msg" maxlength="300" rows="2" class="k19-input py-3" placeholder="{{ __('np. A2 km 312 kierunek Berlin, przebita opona') }}"></textarea>
            <p id="k19-sos-status" role="status" class="mt-4 hidden rounded-xl p-4 font-semibold"></p>
            <a href="{{ route('map.index') }}" class="mt-4 inline-block font-semibold text-k19-red">{{ __('Zobacz wezwania na mapie') }} →</a>

            <script>
            (() => {
                const T = @js(['locating' => __('Pobieram lokalizację…'), 'locErr' => __('Nie udało się pobrać lokalizacji. Włącz GPS i spróbuj ponownie.'), 'error' => __('Nie udało się wysłać wezwania.'), 'call' => __('Przy zagrożeniu życia zadzwoń też pod 112!')]);
                const status = document.getElementById('k19-sos-status');
                const show = (t, ok) => { status.textContent = t; status.className = 'mt-4 rounded-xl p-4 font-semibold ' + (ok ? 'bg-[#E8F6EE] text-[#14532D]' : 'bg-[#FDECEA] text-k19-red'); };
                document.querySelectorAll('[data-problem]').forEach(b => b.addEventListener('click', () => {
                    if (!navigator.geolocation) return show(T.locErr, false);
                    show(T.locating, true);
                    navigator.geolocation.getCurrentPosition(async p => {
                        const r = await fetch(@js(route('map.store')), {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                            body: JSON.stringify({kind: 'sos', problem: b.dataset.problem, message: document.getElementById('k19-sos-msg').value || undefined, lat: +p.coords.latitude.toFixed(6), lng: +p.coords.longitude.toFixed(6), country: 'PL'})}).then(r => r.json()).catch(() => ({}));
                        if (r.ok) show(r.message + (b.dataset.problem === 'life' ? ' ' + T.call : ''), true); else show(r.message || T.error, false);
                    }, () => show(T.locErr, false), {enableHighAccuracy: true, timeout: 15000});
                }));
            })();
            </script>
        @else
            <div class="k19-card mt-6 p-5">
                <p class="font-display text-lg font-bold">{{ __('Wezwanie innych kierowców wymaga konta') }}</p>
                <p class="mt-1 text-k19-muted">{{ __('Zaloguj się i potwierdź numer telefonu, żeby kierowcy mogli do Ciebie oddzwonić.') }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('login') }}" class="k19-btn-dark">{{ __('Zaloguj się') }}</a>
                    <a href="{{ route('register') }}" class="k19-btn-ghost">{{ __('Załóż konto za darmo') }}</a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
