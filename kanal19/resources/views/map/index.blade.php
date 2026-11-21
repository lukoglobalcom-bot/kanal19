@php($M = \App\Models\MapReport::class)
@php($canReport = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasVerifiedPhone()))
<x-layouts.app :title="__('Mapa kierowców')">
    @vite('resources/js/map.js')

    <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-[clamp(30px,4vw,44px)] font-extrabold">{{ __('Mapa kierowców') }}</h1>
                <p class="text-k19-muted">{{ __('Wolne miejsca na parkingach, ceny paliwa, utrudnienia i wezwania SOS – zgłaszane przez kierowców.') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('route.index') }}" class="k19-btn-dark">🚛 {{ __('Planer trasy') }}</a>
                <a href="{{ route('map.sos') }}" class="k19-btn bg-k19-red text-white hover:bg-k19-red-dark">SOS</a>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="{{ __('Warstwy mapy') }}">
            <label class="k19-pill"><input type="checkbox" class="sr-only" data-layer="parking" checked><span>🅿 {{ __('Parkingi') }}</span></label>
            <label class="k19-pill"><input type="checkbox" class="sr-only" data-layer="fuel" checked><span>⛽ {{ __('Ceny paliwa') }}</span></label>
            <label class="k19-pill"><input type="checkbox" class="sr-only" data-layer="road" checked><span>⚠ {{ __('Utrudnienia') }}</span></label>
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
                            <label class="k19-pill"><input type="radio" name="kind" value="road" class="sr-only"><span>⚠ {{ __('Utrudnienie') }}</span></label>
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
                            <div class="flex gap-2">
                                <button type="button" data-park-quick="0" class="k19-pill flex-1 justify-center">⛔ {{ __('Brak miejsc') }}</button>
                                <button type="button" data-park-quick="10" class="k19-pill flex-1 justify-center">✅ {{ __('Dużo miejsc (10+)') }}</button>
                            </div>
                            @foreach (['free_truck' => __('Ciągnik z naczepą'), 'free_rigid' => __('Solówka'), 'free_van' => __('Bus do 3,5 t'), 'free_bus' => __('Autobus')] as $f => $lbl)
                                <div class="flex items-center gap-2">
                                    <label for="r-{{ $f }}" class="flex-1 text-sm font-semibold">{{ $lbl }}</label>
                                    <button type="button" data-step="-1" data-for-input="r-{{ $f }}" class="size-11 rounded-xl border border-k19-line text-xl font-bold" aria-label="−1 {{ $lbl }}">−</button>
                                    <input id="r-{{ $f }}" name="{{ $f }}" type="number" min="0" max="999" inputmode="numeric" class="k19-input w-20 text-center">
                                    <button type="button" data-step="1" data-for-input="r-{{ $f }}" class="size-11 rounded-xl border border-k19-line text-xl font-bold" aria-label="+1 {{ $lbl }}">+</button>
                                </div>
                            @endforeach
                            <div class="flex flex-wrap gap-2">
                                @foreach ($M::PARKING_TAGS as $k => $t)
                                    <label class="k19-pill"><input type="checkbox" name="tags[]" value="{{ $k }}" class="sr-only"><span>{{ $t['icon'] }} {{ __($t['label']) }}</span></label>
                                @endforeach
                            </div>
                            <label class="flex items-center gap-2"><input type="checkbox" name="paid" value="1" class="size-[18px] accent-k19-red">{{ __('Parking płatny') }}</label>
                            <input name="note" maxlength="200" class="k19-input" placeholder="{{ __('Uwagi, np. prysznice, ochrona') }}" aria-label="{{ __('Uwagi') }}">
                        </fieldset>
                        <fieldset data-for="fuel" class="hidden flex-col gap-3">
                            <legend class="k19-label">{{ __('Cena za litr') }} (<span id="k19-cur">PLN</span>)</legend>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="text-sm font-semibold">Diesel<input name="diesel" type="number" step="0.01" min="0" class="k19-input mt-1"></label>
                                <label class="text-sm font-semibold">AdBlue<input name="adblue" type="number" step="0.01" min="0" class="k19-input mt-1"></label>
                                <label class="text-sm font-semibold">{{ __('LNG (za kg)') }}<input name="lng_price" type="number" step="0.01" min="0" class="k19-input mt-1"></label>
                            </div>
                        </fieldset>
                        <fieldset data-for="road" class="hidden flex-col gap-3">
                            <legend class="k19-label">{{ __('Co się dzieje?') }}</legend>
                            <select name="problem" class="k19-input" aria-label="{{ __('Co się dzieje?') }}">
                                @foreach ($M::ROAD_PROBLEMS as $k => $p)<option value="{{ $k }}">{{ __($p['label']) }}</option>@endforeach
                            </select>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="text-sm font-semibold">{{ __('Droga') }}<input name="road" maxlength="12" class="k19-input mt-1" placeholder="A2, S8, DK92"></label>
                                <label class="text-sm font-semibold" data-limit>{{ __('Limit (m / t)') }}<input name="limit" type="number" step="0.1" min="1" max="60" class="k19-input mt-1" placeholder="3.8"></label>
                            </div>
                            <input name="note" maxlength="200" class="k19-input" placeholder="{{ __('np. objazd przez Koło, ruch wahadłowy') }}" aria-label="{{ __('Uwagi') }}">
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
                <x-push-toggle />
                <p class="text-xs text-k19-muted">{{ __('Zgłoszenia parkingów znikają po 6 godzinach, ceny paliwa po 7 dniach, SOS po 6 godzinach.') }} {{ __('Utrudnienia znikają same: korek po 2 h, wypadek po 3 h, zamknięcie po 2 dniach, wiadukty i zakazy po 30 dniach.') }}</p>
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
            'locErr' => __('Nie udało się pobrać lokalizacji.'), 'limit' => __('Limit'), 'rigid' => __('solówki'), 'van' => __('busy'),
            'sNone' => __('Brak miejsc'), 'sSome' => __('Są wolne miejsca'), 'sMany' => __('Dużo miejsc (10+)'), 'confirmed' => __('Potwierdzone przez kierowców:'),
        ]);
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        const map = L.map('k19-map').setView([52.0, 19.4], 6);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);

        const colors = {parking: '#1F6FEB', fuel: '#16181D', sos: '#C8221A', road: '#E8A317'};
        const icons = {parking: 'P', fuel: '⛽', sos: 'SOS', road: '⚠'};
        const layers = {parking: L.layerGroup().addTo(map), fuel: L.layerGroup().addTo(map), sos: L.layerGroup().addTo(map), road: L.layerGroup().addTo(map)};
        const el = (tag, text, cls) => { const e = document.createElement(tag); if (text !== undefined && text !== null) e.textContent = text; if (cls) e.className = cls; return e; };
        const icon = (kind) => L.divIcon({className: '', html: `<span style="display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;padding:0 6px;border-radius:9px;background:${colors[kind]};color:#fff;font:800 13px/1 'Barlow Condensed',sans-serif;box-shadow:0 0 0 2px #fff,0 2px 6px rgba(0,0,0,.35)">${icons[kind]}</span>`, iconSize: [30, 30], iconAnchor: [15, 15]});

        async function post(url, body) {
            const r = await fetch(url, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify(body || {})});
            return r.json().catch(() => ({}));
        }

        function popup(r) {
            const box = el('div'); box.style.minWidth = '190px';
            box.append(el('strong', r.kind === 'sos' ? 'SOS – ' + r.problem_label : r.kind === 'road' ? '⚠ ' + r.problem_label : (r.name || (r.kind === 'fuel' ? '⛽' : 'P'))));
            if (r.kind === 'parking') {
                const d = r.data || {};
                const n = v => v >= 10 ? '10+' : v;
                const counts = [[T.trucks, d.free_truck], [T.rigid, d.free_rigid], [T.van, d.free_van], [T.buses, d.free_bus], [T.cars, d.free_car]].filter(x => x[1] !== null && x[1] !== undefined);
                if (counts.length) box.append(el('div', T.free + ': ' + counts.map(x => x[0] + ' ' + n(x[1])).join(', ')));
                if (d.status) box.append(el('div', {none: '⛔ ' + T.sNone, some: '🅿 ' + T.sSome, many: '✅ ' + T.sMany}[d.status]));
                if (d.tags?.length) box.append(el('div', d.tags.map(t => tagNames[t]).filter(Boolean).join(' · ')));
                box.append(el('div', d.paid ? T.paid : T.freePark));
                if (d.note) box.append(el('div', d.note));
            } else if (r.kind === 'road') {
                const d = r.data || {};
                if (d.road || r.name) box.append(el('div', [d.road, r.name].filter(Boolean).join(' – ')));
                if (d.limit) box.append(el('div', T.limit + ': ' + d.limit + (d.problem === 'low_bridge' ? ' m' : ' t')));
                if (d.note) box.append(el('div', d.note));
                if (r.confirmations) box.append(el('div', T.confirmed + ' ' + r.confirmations));
            } else if (r.kind === 'fuel') {
                const d = r.data || {};
                box.append(el('div', 'Diesel: ' + Number(d.diesel).toFixed(2) + ' ' + d.currency));
                if (d.lng_price) box.append(el('div', 'LNG: ' + Number(d.lng_price).toFixed(2) + ' ' + d.currency + '/kg'));
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

        const tagNames = @js(collect($M::PARKING_TAGS)->map(fn ($t) => $t['icon'].' '.__($t['label'])));
        // liczniki +/− i szybkie przyciski parkingu
        document.querySelectorAll('[data-step]').forEach(b => b.addEventListener('click', () => {
            const i = document.getElementById(b.dataset.forInput); i.value = Math.max(0, Math.min(999, (+i.value || 0) + +b.dataset.step));
        }));
        document.querySelectorAll('[data-park-quick]').forEach(b => b.addEventListener('click', () => {
            const v = b.dataset.parkQuick; document.getElementById('r-free_truck').value = v;
            if (v === '0') ['r-free_rigid', 'r-free_van', 'r-free_bus'].forEach(id => document.getElementById(id).value = 0);
        }));
        let focusId = new URLSearchParams(location.search).get('sos'); // z powiadomienia SOS
        async function load() {
            const data = await fetch(@js(route('map.data')), {headers: {'Accept': 'application/json'}}).then(r => r.json()).catch(() => []);
            Object.values(layers).forEach(l => l.clearLayers());
            data.forEach(r => {
                const m = L.marker([r.lat, r.lng], {icon: icon(r.kind)}).bindPopup(() => popup(r)).addTo(layers[r.kind]);
                if (String(r.id) === focusId) { map.setView([r.lat, r.lng], 12); m.openPopup(); focusId = null; }
            });
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
                form.querySelectorAll('[data-for]').forEach(f => { f.classList.toggle('hidden', f.dataset.for !== kind); f.classList.toggle('flex', f.dataset.for === kind); f.disabled = f.dataset.for !== kind; });
                const lim = form.querySelector('[data-limit]'); lim.hidden = !['low_bridge', 'weight_limit'].includes(form.problem.value);
                document.getElementById('k19-cur').textContent = form.country.selectedOptions[0].dataset.cur;
            };
            form.addEventListener('change', sync); sync();
            form.addEventListener('submit', async e => {
                e.preventDefault();
                if (!picked) return alertMsg(T.needPlace, false);
                const raw = new FormData(form);
                const tags = raw.getAll('tags[]'); raw.delete('tags[]');
                const fd = Object.fromEntries(raw.entries());
                Object.keys(fd).forEach(k => fd[k] === '' && delete fd[k]);
                if (fd.kind === 'parking' && tags.length) fd.tags = tags;
                const res = await post(@js(route('map.store')), {...fd, ...picked});
                if (res.ok) { alertMsg(res.message, true); form.reset(); sync(); load(); }
                else alertMsg(res.message || T.error, false);
            });
        }
    })();
    </script>
</x-layouts.app>
