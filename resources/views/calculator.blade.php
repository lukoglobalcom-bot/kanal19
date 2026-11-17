@php
    // Typowe, orientacyjne spalanie (l/100 km). Kierowca może je zmienić.
    $vehicles = [
        'bus' => [__('Bus do 3,5 t'), 11],
        'solo' => [__('Solówka 12 t'), 22],
        'tractor_empty' => [__('Ciągnik z naczepą – pusty'), 24],
        'tractor_full' => [__('Ciągnik z naczepą – 40 t'), 31],
        'coach' => [__('Autobus / autokar'), 28],
    ];
@endphp
<x-layouts.app :title="__('Kalkulator trasy')">
    <div class="mx-auto max-w-5xl px-4 pt-10 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,44px)] font-extrabold">{{ __('Kalkulator trasy') }}</h1>
        <p class="text-k19-muted">{{ __('Policz paliwo, AdBlue, koszt i czas jazdy z obowiązkowymi przerwami i odpoczynkami.') }}</p>

        <div class="mt-6 flex flex-wrap items-start gap-6">
            <form id="k19-calc" class="k19-card flex flex-[1_1_320px] flex-col gap-4 p-6" onsubmit="return false">
                <div>
                    <label for="c-km" class="k19-label">{{ __('Długość trasy (km)') }}</label>
                    <input id="c-km" type="number" min="1" value="1000" class="k19-input">
                </div>
                <div>
                    <label for="c-veh" class="k19-label">{{ __('Pojazd i obciążenie') }}</label>
                    <select id="c-veh" class="k19-input">
                        @foreach ($vehicles as $k => [$label, $l])<option value="{{ $l }}" @selected($k === 'tractor_full')>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="c-cons" class="k19-label">{{ __('Spalanie (l/100 km)') }}</label>
                        <input id="c-cons" type="number" step="0.1" min="1" value="31" class="k19-input">
                    </div>
                    <div>
                        <label for="c-speed" class="k19-label">{{ __('Średnia prędkość (km/h)') }}</label>
                        <input id="c-speed" type="number" min="10" max="130" value="70" class="k19-input">
                    </div>
                    <div>
                        <label for="c-price" class="k19-label">{{ __('Cena paliwa za litr') }}</label>
                        <input id="c-price" type="number" step="0.01" min="0" value="6.20" class="k19-input">
                    </div>
                    <div>
                        <label for="c-cur" class="k19-label">{{ __('Waluta') }}</label>
                        <select id="c-cur" class="k19-input"><option>PLN</option><option>EUR</option><option>CZK</option><option>HUF</option><option>RON</option></select>
                    </div>
                    <div>
                        <label for="c-ad" class="k19-label">{{ __('AdBlue (% paliwa)') }}</label>
                        <input id="c-ad" type="number" step="0.5" min="0" max="15" value="5" class="k19-input">
                    </div>
                    <div>
                        <label for="c-adprice" class="k19-label">{{ __('Cena AdBlue za litr') }}</label>
                        <input id="c-adprice" type="number" step="0.01" min="0" value="3.50" class="k19-input">
                    </div>
                </div>
                <p class="text-xs text-k19-muted">{{ __('Spalanie i ceny są orientacyjne – wpisz własne. Opłaty drogowe nie są wliczone.') }}</p>
            </form>

            <section class="flex flex-[999_1_420px] flex-col gap-4" aria-live="polite">
                <div class="grid grid-cols-2 gap-3">
                    <div class="k19-card p-5"><p class="text-sm font-semibold uppercase tracking-wide text-k19-muted">{{ __('Paliwo') }}</p><p class="font-display text-3xl font-extrabold" id="o-fuel">–</p><p class="text-k19-muted" id="o-fuelcost"></p></div>
                    <div class="k19-card p-5"><p class="text-sm font-semibold uppercase tracking-wide text-k19-muted">AdBlue</p><p class="font-display text-3xl font-extrabold" id="o-ad">–</p><p class="text-k19-muted" id="o-adcost"></p></div>
                    <div class="col-span-2 rounded-2xl bg-k19-ink p-5 text-white"><p class="text-sm font-semibold uppercase tracking-wide text-[#C9CCD3]">{{ __('Koszt razem') }}</p><p class="font-display text-4xl font-extrabold" id="o-total">–</p><p class="text-[#C9CCD3]" id="o-perkm"></p></div>
                </div>
                <div class="k19-card p-5">
                    <p class="font-display text-xl font-bold">{{ __('Czas pracy kierowcy') }}</p>
                    <dl class="mt-3 grid grid-cols-[1fr_auto] gap-x-4 gap-y-2 text-[15px]">
                        <dt class="text-k19-muted">{{ __('Czas samej jazdy') }}</dt><dd class="font-semibold" id="o-drive"></dd>
                        <dt class="text-k19-muted">{{ __('Przerwy 45 min (po 4,5 h jazdy)') }}</dt><dd class="font-semibold" id="o-breaks"></dd>
                        <dt class="text-k19-muted">{{ __('Dni jazdy (maks. 9 h dziennie)') }}</dt><dd class="font-semibold" id="o-days"></dd>
                        <dt class="text-k19-muted">{{ __('Odpoczynki dobowe 11 h') }}</dt><dd class="font-semibold" id="o-rests"></dd>
                        <dt class="font-semibold">{{ __('Cała trasa – orientacyjnie') }}</dt><dd class="font-display text-xl font-extrabold" id="o-total-time"></dd>
                    </dl>
                    <p class="mt-3 text-xs text-k19-muted">{{ __('Według rozporządzenia (WE) nr 561/2006: przerwa 45 min po 4,5 h jazdy, do 9 h jazdy dziennie (dwa razy w tygodniu 10 h), odpoczynek dobowy 11 h (skrócony 9 h). Wynik jest orientacyjny – zawsze kieruj się tachografem.') }}</p>
                </div>
            </section>
        </div>
    </div>

    <script>
    (() => {
        const $ = (id) => document.getElementById(id);
        const fmt = (n, d = 0) => n.toLocaleString(document.documentElement.lang, {minimumFractionDigits: d, maximumFractionDigits: d});
        const hm = (h) => { const m = Math.round(h * 60); return Math.floor(m / 60) + ' h ' + String(m % 60).padStart(2, '0') + ' min'; };
        $('c-veh').addEventListener('change', () => { $('c-cons').value = $('c-veh').value; calc(); });
        document.querySelectorAll('#k19-calc input, #k19-calc select').forEach((el) => el.addEventListener('input', calc));
        function calc() {
            const km = Math.max(0, +$('c-km').value), cons = +$('c-cons').value, speed = Math.max(1, +$('c-speed').value);
            const price = +$('c-price').value, cur = $('c-cur').value, ad = +$('c-ad').value / 100, adPrice = +$('c-adprice').value;
            const fuel = km * cons / 100, adL = fuel * ad, cost = fuel * price + adL * adPrice;
            $('o-fuel').textContent = fmt(fuel) + ' l';
            $('o-fuelcost').textContent = fmt(fuel * price, 2) + ' ' + cur;
            $('o-ad').textContent = fmt(adL, 1) + ' l';
            $('o-adcost').textContent = fmt(adL * adPrice, 2) + ' ' + cur;
            $('o-total').textContent = fmt(cost, 2) + ' ' + cur;
            $('o-perkm').textContent = (km ? fmt(cost / km, 2) : '0') + ' ' + cur + ' / km';

            const drive = km / speed;                       // godziny jazdy
            const days = Math.max(1, Math.ceil(drive / 9));  // maks. 9 h jazdy dziennie
            let breaks = 0, left = drive;
            for (let d = 0; d < days; d++) { const today = Math.min(9, left); breaks += Math.max(0, Math.ceil(today / 4.5) - 1); left -= today; }
            const rests = days - 1;
            $('o-drive').textContent = hm(drive);
            $('o-breaks').textContent = breaks + ' × 45 min';
            $('o-days').textContent = days;
            $('o-rests').textContent = rests + ' × 11 h';
            $('o-total-time').textContent = hm(drive + breaks * 0.75 + rests * 11);
        }
        calc();
    })();
    </script>
</x-layouts.app>
