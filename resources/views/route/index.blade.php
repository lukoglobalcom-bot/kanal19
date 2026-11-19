<x-layouts.app :title="__('Planer trasy dla ciężarówek')">
    @vite('resources/js/map.js')

    <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,44px)] font-extrabold">🚛 {{ __('Planer trasy dla ciężarówek') }}</h1>
        <p class="text-k19-muted">{{ __('Trasa liczona dla wymiarów i masy Twojego pojazdu. Omijamy zamknięte drogi, niskie wiadukty i limity masy zgłoszone przez kierowców.') }}</p>

        @unless ($enabled)
            <p class="mt-4 rounded-xl border border-[#E8A317] bg-[#FFF6E0] p-4 font-semibold">
                {{ __('Planer tras startuje wkrótce – uruchamiamy własny serwer map.') }}
                <span class="font-normal">{{ __('Już teraz możesz wyszukać miejsca i zobaczyć utrudnienia na mapie.') }}</span>
            </p>
        @endunless

        <div class="mt-5 flex flex-wrap items-start gap-6">
            <form id="k19-route" class="k19-card flex flex-[1_1_320px] flex-col gap-4 p-5" novalidate>
                @foreach (['start' => __('Skąd'), 'via' => __('Przez (opcjonalnie)'), 'end' => __('Dokąd')] as $key => $label)
                    <div data-point="{{ $key }}">
                        <label for="p-{{ $key }}" class="k19-label">{{ $label }}</label>
                        <div class="flex gap-2">
                            <input id="p-{{ $key }}" class="k19-input min-w-0 flex-1" autocomplete="off" placeholder="{{ __('Miasto, adres, firma') }}">
                            <button type="button" class="k19-btn-dark shrink-0 px-3" data-search aria-label="{{ __('Szukaj') }}">🔍</button>
                            <button type="button" class="k19-pill shrink-0" data-pick title="{{ __('Wskaż na mapie') }}" aria-label="{{ __('Wskaż na mapie') }}">📍</button>
                        </div>
                        <ul class="mt-1 hidden flex-col overflow-hidden rounded-xl border border-k19-line bg-white text-sm" data-results></ul>
                    </div>
                @endforeach

                <fieldset class="flex flex-col gap-3">
                    <legend class="k19-label">{{ __('Pojazd') }}</legend>
                    <select id="k19-preset" class="k19-input" aria-label="{{ __('Pojazd') }}">
                        @foreach ($presets as $k => $p)<option value="{{ $k }}">{{ __($p['label']) }}</option>@endforeach
                    </select>
                    <div class="grid grid-cols-3 gap-2 text-sm font-semibold">
                        <label class="flex flex-col justify-end">{{ __('Wysokość (m)') }}<input name="height" type="number" step="0.05" min="1.5" max="5" class="k19-input mt-1"></label>
                        <label class="flex flex-col justify-end">{{ __('Szerokość (m)') }}<input name="width" type="number" step="0.05" min="1.5" max="3.5" class="k19-input mt-1"></label>
                        <label class="flex flex-col justify-end">{{ __('Długość (m)') }}<input name="length" type="number" step="0.1" min="3" max="30" class="k19-input mt-1"></label>
                        <label class="flex flex-col justify-end">{{ __('Masa (t)') }}<input name="weight" type="number" step="0.5" min="1" max="60" class="k19-input mt-1"></label>
                        <label class="flex flex-col justify-end">{{ __('Nacisk osi (t)') }}<input name="axle_load" type="number" step="0.5" min="1" max="20" class="k19-input mt-1"></label>
                        <label class="flex flex-col justify-end">{{ __('Osie') }}<input name="axle_count" type="number" step="1" min="2" max="10" class="k19-input mt-1"></label>
                    </div>
                    <label class="flex items-center gap-2"><input type="checkbox" name="hazmat" value="1" class="size-[18px] accent-k19-red">{{ __('Towary niebezpieczne (ADR)') }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="avoid_tolls" value="1" class="size-[18px] accent-k19-red">{{ __('Unikaj dróg płatnych') }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="avoid_ferries" value="1" class="size-[18px] accent-k19-red">{{ __('Unikaj promów') }}</label>
                </fieldset>

                <p id="k19-rmsg" role="status" class="hidden rounded-xl p-3 text-sm font-semibold"></p>
                <button class="k19-btn-red">{{ __('Wyznacz trasę') }}</button>
            </form>

            <div class="flex flex-[999_1_560px] flex-col gap-4">
                <div id="k19-map" class="z-0 h-[60vh] min-h-[380px] overflow-hidden rounded-2xl border border-k19-line"></div>
                <div id="k19-routes" class="flex flex-wrap gap-3"></div>
                <ol id="k19-steps" class="k19-card hidden max-h-[50vh] list-decimal flex-col gap-1 overflow-y-auto p-5 pl-10 text-sm"></ol>
                <p class="text-xs text-k19-muted">{{ __('Trasa ma charakter pomocniczy. Zawsze stosuj się do znaków drogowych. Dane mapy: © OpenStreetMap.') }}</p>
            </div>
        </div>
    </div>

    <script type="module">
    (() => {
        const T = @js([
            'start' => __('Start'), 'via' => __('Przez'), 'end' => __('Cel'), 'route' => __('Trasa'), 'toll' => __('drogi płatne'),
            'ferry' => __('prom'), 'need' => __('Ustaw start i cel – wyszukaj adres albo wskaż na mapie.'),
            'nothing' => __('Nic nie znaleziono.'), 'pickNow' => __('Kliknij na mapie, aby ustawić punkt.'),
            'avoided' => __('Ominięte utrudnienia:'), 'error' => __('Nie udało się wyznaczyć trasy.'), 'wait' => __('Liczę trasę…'),
        ]);
        const presets = @js($presets);
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        const form = document.getElementById('k19-route');
        const map = L.map('k19-map').setView([52.0, 19.4], 6);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
        const el = (tag, text, cls) => { const e = document.createElement(tag); if (text != null) e.textContent = text; if (cls) e.className = cls; return e; };
        const msg = (text, ok) => { const m = document.getElementById('k19-rmsg'); m.textContent = text; m.className = 'rounded-xl p-3 text-sm font-semibold ' + (ok ? 'bg-[#E8F6EE] text-[#14532D]' : 'bg-[#FDECEA] text-k19-red'); if (!text) m.classList.add('hidden'); };

        // Utrudnienia zgłoszone przez kierowców
        const roadLayer = L.layerGroup().addTo(map);
        fetch(@js(route('map.data')), {headers: {Accept: 'application/json'}}).then(r => r.json()).then(list => list.filter(r => r.kind === 'road').forEach(r => {
            const d = r.data || {};
            L.marker([r.lat, r.lng], {icon: L.divIcon({className: '', html: '<span style="display:inline-flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:8px;background:#E8A317;color:#16181D;font-weight:800;box-shadow:0 0 0 2px #fff">⚠</span>', iconSize: [28, 28], iconAnchor: [14, 14]})})
                .bindPopup(() => { const b = el('div'); b.append(el('strong', r.problem_label)); [d.road, d.limit ? d.limit + (d.problem === 'low_bridge' ? ' m' : ' t') : null, d.note].filter(Boolean).forEach(t => b.append(el('div', t))); return b; })
                .addTo(roadLayer);
        })).catch(() => {});

        // Punkty trasy
        const points = {start: null, via: null, end: null};
        const markers = {};
        const colors = {start: '#14532D', via: '#1F6FEB', end: '#C8221A'};
        function setPoint(key, lat, lng, name) {
            points[key] = {lat: +(+lat).toFixed(6), lng: +(+lng).toFixed(6)};
            const label = T[key];
            const icon = L.divIcon({className: '', html: `<span style="display:inline-flex;padding:4px 8px;border-radius:8px;background:${colors[key]};color:#fff;font:800 12px/1 sans-serif;box-shadow:0 0 0 2px #fff;white-space:nowrap">${label}</span>`, iconAnchor: [10, 24]});
            markers[key] ? markers[key].setLatLng([lat, lng]) : (markers[key] = L.marker([lat, lng], {icon}).addTo(map));
            const input = document.getElementById('p-' + key);
            input.value = name || (points[key].lat + ', ' + points[key].lng);
        }

        let picking = null;
        map.on('click', e => { if (picking) { setPoint(picking, e.latlng.lat, e.latlng.lng); picking = null; msg(''); } });

        document.querySelectorAll('[data-point]').forEach(box => {
            const key = box.dataset.point, input = box.querySelector('input'), list = box.querySelector('[data-results]');
            box.querySelector('[data-pick]').onclick = () => { picking = key; msg(T.pickNow, true); };
            input.addEventListener('input', () => { points[key] = null; if (!input.value && markers[key]) { map.removeLayer(markers[key]); delete markers[key]; } });
            input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); search(); } });
            box.querySelector('[data-search]').onclick = search;
            async function search() {
                if (input.value.trim().length < 3) return;
                const r = await fetch(@js(route('route.search')) + '?q=' + encodeURIComponent(input.value.trim()), {headers: {Accept: 'application/json'}});
                const data = await r.json().catch(() => []);
                list.replaceChildren();
                if (!r.ok) { msg(data.message || T.nothing, false); return; }
                if (!data.length) { list.append(el('li', T.nothing, 'p-3 text-k19-muted')); }
                data.forEach(p => {
                    const li = el('li'); const b = el('button', p.name, 'w-full p-3 text-left hover:bg-k19-paper'); b.type = 'button';
                    b.onclick = () => { setPoint(key, p.lat, p.lng, p.name); list.classList.add('hidden'); list.classList.remove('flex'); map.setView([p.lat, p.lng], 11); };
                    li.append(b); list.append(li);
                });
                list.classList.remove('hidden'); list.classList.add('flex');
            }
        });

        // Pojazd – zapamiętany w przeglądarce
        const vehFields = ['height', 'width', 'length', 'weight', 'axle_load', 'axle_count'];
        const preset = document.getElementById('k19-preset');
        const applyPreset = () => vehFields.forEach(f => form[f].value = presets[preset.value][f]);
        let saved = null; try { saved = JSON.parse(localStorage.getItem('k19-vehicle') || 'null'); } catch (e) {}
        if (saved && presets[saved.preset]) { preset.value = saved.preset; vehFields.forEach(f => form[f].value = saved[f] ?? presets[saved.preset][f]); ['hazmat', 'avoid_tolls', 'avoid_ferries'].forEach(f => form[f].checked = !!saved[f]); }
        else applyPreset();
        preset.onchange = applyPreset;

        // Dekodowanie kształtu trasy (polyline, precyzja 6)
        function decode(str) {
            let i = 0, lat = 0, lng = 0; const out = [];
            while (i < str.length) {
                for (const which of [0, 1]) {
                    let b, shift = 0, result = 0;
                    do { b = str.charCodeAt(i++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
                    const v = (result & 1) ? ~(result >> 1) : (result >> 1);
                    which === 0 ? (lat += v) : (lng += v);
                }
                out.push([lat / 1e6, lng / 1e6]);
            }
            return out;
        }

        const routeLayer = L.layerGroup().addTo(map);
        let routes = [];
        const hm = (min) => Math.floor(min / 60) + ' h ' + String(min % 60).padStart(2, '0') + ' min';
        function show(idx) {
            routeLayer.clearLayers();
            routes.forEach((r, i) => {
                const line = L.polyline(r.shapes.flatMap(decode), {color: i === idx ? '#C8221A' : '#8A8F98', weight: i === idx ? 6 : 4, opacity: i === idx ? .9 : .6}).addTo(routeLayer);
                line.on('click', () => show(i));
                if (i === idx) { line.bringToFront(); map.fitBounds(line.getBounds(), {padding: [30, 30]}); }
            });
            const box = document.getElementById('k19-routes'); box.replaceChildren();
            routes.forEach((r, i) => {
                const b = el('button', null, 'k19-card px-4 py-3 text-left ' + (i === idx ? 'ring-2 ring-k19-red' : '')); b.type = 'button';
                b.append(el('strong', T.route + ' ' + (i + 1) + ': ' + r.km + ' km', 'block font-display text-xl'), el('span', hm(r.minutes) + (r.toll ? ' · ' + T.toll : '') + (r.ferry ? ' · ' + T.ferry : ''), 'text-sm text-k19-muted'));
                b.onclick = () => show(i); box.append(b);
            });
            const steps = document.getElementById('k19-steps'); steps.replaceChildren();
            routes[idx].steps.forEach(s => steps.append(el('li', s.text + (s.km ? ' (' + s.km + ' km)' : ''))));
            steps.classList.remove('hidden'); steps.classList.add('flex');
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();
            if (!points.start || !points.end) return msg(T.need, false);
            const veh = {preset: preset.value};
            vehFields.forEach(f => veh[f] = form[f].value);
            ['hazmat', 'avoid_tolls', 'avoid_ferries'].forEach(f => veh[f] = form[f].checked);
            try { localStorage.setItem('k19-vehicle', JSON.stringify(veh)); } catch (err) {}
            msg(T.wait, true);
            const body = {...veh, points: [points.start, points.via, points.end].filter(Boolean)};
            delete body.preset;
            const r = await fetch(@js(route('route.plan')), {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify(body)});
            const data = await r.json().catch(() => ({}));
            if (!r.ok) return msg(data.message || T.error, false);
            routes = data.routes; msg(data.avoided ? T.avoided + ' ' + data.avoided : '', true);
            show(0);
        });
    })();
    </script>
</x-layouts.app>
