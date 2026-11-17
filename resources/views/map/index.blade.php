@php($M = \App\Models\MapReport::class)
@php($canReport = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasVerifiedPhone()))
<x-layouts.app :title="__('Mapa kierowców')">
    @vite('resources/js/map.js')

    <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-[clamp(30px,4vw,44px)] font-extrabold">{{ __('Mapa kierowców') }}</h1>
                <p class="text-k19-muted">{{ __('Wolne miejsca na parkingach, ceny paliwa i wezwania SOS – zgłaszane przez kierowców.') }}</p>
            </div>
            <a href="{{ route('map.sos') }}" class="k19-btn bg-k19-red text-white hover:bg-k19-red-dark">SOS</a>
        </div>

        <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="{{ __('Warstwy mapy') }}">
            <label class="k19-pill"><input type="checkbox" class="sr-only" data-layer="parking" checked><span>🅿 {{ __('Parkingi') }}</span></label>
            <label class="k19-pill"><input type="checkbox" class="sr-only" data-layer="fuel" checked><span>⛽ {{ __('Ceny paliwa') }}</span></label>
            <label class="k19-pill"><input type="checkbox" class="sr-only" data-layer="sos" checked><span>🆘 SOS</span></label>
            <button type="button" id="k19-locate" class="k19-pill">📍 {{ __('Moja lokalizacja') }}</button>
        </div>

        <div class="mt-4 flex flex-wrap items-start gap-6">
            <div id="k19-map" class="z-0 h-[60vh] min-h-[380px] flex-[999_1_560px] overflow-hidden rounded-2xl border border-k19-line"></div>

            <aside class="flex flex-[1_1_300px] flex-col gap-4">
                @if ($canReport)
                    <form id="k19-report" class="k19-card flex flex-col gap-4 p-5" novalidate>
                        <p class="font-display text-xl font-bold">{{ __('Dodaj zgłoszenie') }}</p>
                        <div class="flex flex-wrap gap-2">
                            <label class="k19-pill"><input type="radio" name="kind" value="parking" class="sr-only" checked><span>🅿 {{ __('Parking') }}</span></label>
                            <label class="k19-pill"><input type="radio" name="kind" value="fuel" class="sr-only"><span>⛽ {{ __('Paliwo') }}</span></label>
                        </div>
                        <p id="k19-where" class="rounded-xl bg-k19-paper p-3 text-sm text-k19-muted">{{ __('Kliknij na mapie miejsce albo użyj „Moja lokalizacja”.') }}</p>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label for="r-name" class="k19-label">{{ __('Nazwa miejsca') }}</label>
                                <input id="r-name" name="name" maxlength="120" class="k19-input" placeholder="{{ __('np. MOP Wiskitki') }}">
                            </div>
                            <div>
                                <label for="r-country" class="k19-label">{{ __('Kraj') }}</label>
                                <select id="r-country" name="country" class="k19-input">
                                    @foreach ($M::COUNTRIES as $c)<option value="{{ $c }}" data-cur="{{ $M::currencyFor($c) }}">{{ $c }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <fieldset data-for="parking" class="flex flex-col gap-3">
                            <legend class="k19-label">{{ __('Wolne miejsca teraz') }}</legend>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="text-sm font-semibold">{{ __('Ciężarowe') }}<input name="free_truck" type="number" min="0" max="999" class="k19-input mt-1"></label>
                                <label class="text-sm font-semibold">{{ __('Autobusy') }}<input name="free_bus" type="number" min="0" max="999" class="k19-input mt-1"></label>
                                <label class="text-sm font-semibold">{{ __('Osobowe') }}<input name="free_car" type="number" min="0" max="999" class="k19-input mt-1"></label>
                            </div>
                            <label class="flex items-center gap-2"><input type="checkbox" name="paid" value="1" class="size-[18px] accent-k19-red">{{ __('Parking płatny') }}</label>
                            <input name="note" maxlength="200" class="k19-input" placeholder="{{ __('Uwagi, np. prysznice, ochrona') }}" aria-label="{{ __('Uwagi') }}">
                        </fieldset>
                        <fieldset data-for="fuel" class="hidden flex-col gap-3">
                            <legend class="k19-label">{{ __('Cena za litr') }} (<span id="k19-cur">PLN</span>)</legend>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="text-sm font-semibold">Diesel<input name="diesel" type="number" step="0.01" min="0" class="k19-input mt-1"></label>
                                <label class="text-sm font-semibold">AdBlue<input name="adblue" type="number" step="0.01" min="0" class="k19-input mt-1"></label>
                            </div>
                        </fieldset>
                        <p id="k19-msg" role="status" class="hidden rounded-xl p-3 text-sm font-semibold"></p>
                        <button class="k19-btn-red">{{ __('Wyślij zgłoszenie') }}</button>
                    </form>
                @else
                    <div class="k19-card p-5">
                        <p class="font-display text-lg font-bold">{{ __('Chcesz dodawać zgłoszenia?') }}</p>
                        <p class="mt-1 text-k19-muted">{{ __('Zaloguj się i potwierdź numer telefonu – wtedy zgłosisz wolne miejsca, ceny paliwa i wezwiesz pomoc.') }}</p>
                        <a href="{{ route('login') }}" class="k19-btn-dark mt-3">{{ __('Zaloguj się') }}</a>
                    </div>
                @endif
                <p class="text-xs text-k19-muted">{{ __('Zgłoszenia parkingów znikają po 6 godzinach, ceny paliwa po 7 dniach, SOS po 6 godzinach.') }}</p>
            </aside>
        </div>
    </div>

    <script type="module">
    (() => {
        const T = @js([
            'free' => __('Wolne miejsca'), 'trucks' => __('Ciężarowe'), 'buses' => __('Autobusy'), 'cars' => __('Osobowe'),
            'paid' => __('Parking płatny'), 'freePark' => __('Parking bezpłatny'), 'ago' => __('min temu'),
            'help' => __('Jadę pomóc'), 'helping' => __('Jedziesz z pomocą'), 'helpers' => __('Jedzie z pomocą:'),
            'close' => __('Zamknij zgłoszenie'), 'call' => __('Zadzwoń'), 'picked' => __('Wybrane miejsce:'),
            'needPlace' => __('Najpierw wybierz miejsce na mapie.'), 'error' => __('Nie udało się wysłać. Sprawdź dane.'),
            'locErr' => __('Nie udało się pobrać lokalizacji.'),
        ]);
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        const map = L.map('k19-map').setView([52.0, 19.4], 6);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);

        const colors = {parking: '#1F6FEB', fuel: '#16181D', sos: '#C8221A'};
        const icons = {parking: 'P', fuel: '⛽', sos: 'SOS'};
        const layers = {parking: L.layerGroup().addTo(map), fuel: L.layerGroup().addTo(map), sos: L.layerGroup().addTo(map)};
        const el = (tag, text, cls) => { const e = document.createElement(tag); if (text !== undefined && text !== null) e.textContent = text; if (cls) e.className = cls; return e; };
        const icon = (kind) => L.divIcon({className: '', html: `<span style="display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;padding:0 6px;border-radius:9px;background:${colors[kind]};color:#fff;font:800 13px/1 'Barlow Condensed',sans-serif;box-shadow:0 0 0 2px #fff,0 2px 6px rgba(0,0,0,.35)">${icons[kind]}</span>`, iconSize: [30, 30], iconAnchor: [15, 15]});

        async function post(url, body) {
            const r = await fetch(url, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify(body || {})});
            return r.json().catch(() => ({}));
        }

        function popup(r) {
            const box = el('div'); box.style.minWidth = '190px';
            box.append(el('strong', r.kind === 'sos' ? 'SOS – ' + r.problem_label : (r.name || (r.kind === 'fuel' ? '⛽' : 'P'))));
            if (r.kind === 'parking') {
                const d = r.data || {};
                box.append(el('div', T.free + ': ' + [[T.trucks, d.free_truck], [T.buses, d.free_bus], [T.cars, d.free_car]].filter(x => x[1] !== null && x[1] !== undefined).map(x => x[0] + ' ' + x[1]).join(', ')));
                box.append(el('div', d.paid ? T.paid : T.freePark));
                if (d.note) box.append(el('div', d.note));
            } else if (r.kind === 'fuel') {
                const d = r.data || {};
                box.append(el('div', 'Diesel: ' + Number(d.diesel).toFixed(2) + ' ' + d.currency));
                if (d.adblue) box.append(el('div', 'AdBlue: ' + Number(d.adblue).toFixed(2) + ' ' + d.currency));
            } else {
                if (r.message) box.append(el('div', r.message));
                if (r.who) box.append(el('div', r.who));
                if (r.phone) { const a = el('a', T.call + ': ' + r.phone); a.href = 'tel:' + r.phone.replace(/\s+/g, ''); a.style.fontWeight = '800'; a.style.color = '#C8221A'; box.append(a); }
                box.append(el('div', T.helpers + ' ' + r.helpers));
                @auth
                if (!r.mine) {
                    const b = el('button', r.helping ? T.helping : T.help); b.className = 'k19-btn-red'; b.style.minHeight = '36px'; b.style.marginTop = '6px'; b.disabled = r.helping;
                    b.onclick = async () => { await post(`{{ url('/mapa/sos') }}/${r.id}/pomoc`); load(); };
                    box.append(b);
                }
                @endauth
            }
            box.append(el('div', r.minutes + ' ' + T.ago, 'text-xs'));
            if (r.mine) { const c = el('button', T.close); c.style.textDecoration = 'underline'; c.onclick = async () => { await post(`{{ url('/mapa/zgloszenia') }}/${r.id}/zamknij`); load(); }; box.append(c); }
            return box;
        }

        async function load() {
            const data = await fetch(@js(route('map.data')), {headers: {'Accept': 'application/json'}}).then(r => r.json()).catch(() => []);
            Object.values(layers).forEach(l => l.clearLayers());
            data.forEach(r => L.marker([r.lat, r.lng], {icon: icon(r.kind)}).bindPopup(() => popup(r)).addTo(layers[r.kind]));
        }
        load(); setInterval(load, 60000);

        document.querySelectorAll('[data-layer]').forEach(cb => cb.addEventListener('change', () => cb.checked ? layers[cb.dataset.layer].addTo(map) : map.removeLayer(layers[cb.dataset.layer])));

        let picked = null, pickMarker = null;
        function pick(lat, lng) {
            picked = {lat: +lat.toFixed(6), lng: +lng.toFixed(6)};
            pickMarker ? pickMarker.setLatLng([lat, lng]) : (pickMarker = L.circleMarker([lat, lng], {radius: 9, color: '#C8221A'}).addTo(map));
            const w = document.getElementById('k19-where'); if (w) w.textContent = T.picked + ' ' + picked.lat + ', ' + picked.lng;
        }
        map.on('click', e => pick(e.latlng.lat, e.latlng.lng));
        document.getElementById('k19-locate').onclick = () => navigator.geolocation
            ? navigator.geolocation.getCurrentPosition(p => { pick(p.coords.latitude, p.coords.longitude); map.setView([p.coords.latitude, p.coords.longitude], 13); }, () => alertMsg(T.locErr, false))
            : alertMsg(T.locErr, false);

        const form = document.getElementById('k19-report');
        function alertMsg(text, ok) { const m = document.getElementById('k19-msg'); if (!m) return; m.textContent = text; m.className = 'rounded-xl p-3 text-sm font-semibold ' + (ok ? 'bg-[#E8F6EE] text-[#14532D]' : 'bg-[#FDECEA] text-k19-red'); }
        if (form) {
            const sync = () => {
                const kind = form.kind.value;
                form.querySelectorAll('[data-for]').forEach(f => { f.classList.toggle('hidden', f.dataset.for !== kind); f.classList.toggle('flex', f.dataset.for === kind); });
                document.getElementById('k19-cur').textContent = form.country.selectedOptions[0].dataset.cur;
            };
            form.addEventListener('change', sync); sync();
            form.addEventListener('submit', async e => {
                e.preventDefault();
                if (!picked) return alertMsg(T.needPlace, false);
                const fd = Object.fromEntries(new FormData(form).entries());
                Object.keys(fd).forEach(k => fd[k] === '' && delete fd[k]);
                const res = await post(@js(route('map.store')), {...fd, ...picked});
                if (res.ok) { alertMsg(res.message, true); form.reset(); sync(); load(); }
                else alertMsg(res.message || T.error, false);
            });
        }
    })();
    </script>
</x-layouts.app>
