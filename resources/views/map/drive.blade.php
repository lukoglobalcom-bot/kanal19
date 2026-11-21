@php
    $user = auth()->user();
    $canReport = $user && ($user->isAdmin() || $user->hasVerifiedPhone());
    $voice = $canReport && \App\Services\VoiceToken::enabled();
    $quick = [
        'jam' => ['🚗', __('Korek')], 'accident' => ['💥', __('Wypadek')], 'police' => ['👮', __('Kontrola')],
        'roadwork' => ['🚧', __('Roboty drogowe')], 'closure' => ['⛔', __('Droga zamknięta')],
    ];
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Tryb jazdy') }} – kanal19.pl</title>
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#16181D">
    <meta name="apple-mobile-web-app-capable" content="yes">
    @vite(['resources/css/app.css', 'resources/js/map.js', 'resources/js/tacho.js'])
    @if ($voice) @vite('resources/js/cb-voice.js') @endif
    <style>
        html, body { height: 100%; overflow: hidden; overscroll-behavior: none; }
        .k19-q { min-height: 64px; }
        .leaflet-control-attribution { font-size: 10px; }
    </style>
</head>
<body class="bg-k19-ink font-sans text-white antialiased">
<div class="fixed inset-0 flex flex-col">
    {{-- Górny pasek: ostrzeżenie --}}
    <div id="k19-alert" hidden class="z-[500] flex items-center gap-3 bg-[#E8A317] px-4 py-3 text-k19-ink" role="alert">
        <span id="k19-alert-icon" class="text-4xl">⚠</span>
        <div class="min-w-0 flex-1">
            <p id="k19-alert-text" class="line-clamp-2 font-display text-xl font-extrabold leading-tight"></p>
            <p id="k19-alert-sub" class="truncate text-sm font-semibold"></p>
        </div>
        <span id="k19-alert-dist" class="shrink-0 font-display text-3xl font-extrabold"></span>
    </div>

    <div class="relative flex-1">
        <div id="k19-map" class="absolute inset-0 z-0"></div>

        {{-- Prędkość --}}
        <div class="absolute bottom-4 left-4 z-[400] flex size-24 flex-col items-center justify-center rounded-full border-4 border-white bg-k19-ink shadow-xl">
            <span id="k19-speed" class="font-display text-4xl font-extrabold leading-none">–</span>
            <span class="text-xs font-bold text-[#C9CDD3]">km/h</span>
        </div>
        <a href="{{ route('pause') }}" id="k19-tacho" class="absolute bottom-7 left-32 z-[400] flex min-h-14 items-center gap-2 rounded-2xl bg-k19-ink px-4 font-display text-2xl font-extrabold text-white no-underline shadow-xl" aria-label="{{ __('Do obowiązkowej przerwy') }}">☕ <span id="k19-tacho-left">–</span></a>

        <div class="absolute right-3 top-3 z-[400] flex flex-col gap-2">
            <a href="{{ route('app') }}" class="flex size-12 items-center justify-center rounded-xl bg-k19-ink text-2xl shadow-lg" aria-label="{{ __('Zamknij tryb jazdy') }}">✕</a>
            <button type="button" id="k19-center" class="flex size-12 items-center justify-center rounded-xl bg-k19-ink text-2xl shadow-lg" aria-label="{{ __('Pokaż moją pozycję') }}">◎</button>
            <button type="button" id="k19-sound" class="flex size-12 items-center justify-center rounded-xl bg-k19-ink text-2xl shadow-lg" aria-label="{{ __('Dźwięk ostrzeżeń') }}">🔊</button>
        </div>

        <p id="k19-status" class="absolute left-1/2 top-3 z-[400] -translate-x-1/2 rounded-full bg-k19-ink/90 px-4 py-2 text-sm font-semibold shadow-lg">{{ __('Szukam sygnału GPS…') }}</p>

        {{-- „Nadal jest?” po minięciu utrudnienia --}}
        <div id="k19-confirm" hidden class="absolute inset-x-3 bottom-32 z-[440] rounded-2xl bg-white p-4 text-k19-ink shadow-2xl">
            <p class="text-center font-display text-xl font-bold"><span id="k19-confirm-text"></span> – {{ __('nadal jest?') }}</p>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <button type="button" data-still="1" class="k19-q rounded-2xl bg-[#14532D] font-display text-xl font-bold text-white">✓ {{ __('Tak, jest') }}</button>
                <button type="button" data-still="0" class="k19-q rounded-2xl bg-k19-paper font-display text-xl font-bold">✕ {{ __('Już nie ma') }}</button>
            </div>
        </div>

        {{-- Panel szybkiego zgłoszenia --}}
        <div id="k19-quick" hidden class="absolute inset-x-0 bottom-0 z-[450] rounded-t-3xl bg-k19-ink p-4 shadow-2xl">
            <p class="mb-3 text-center font-display text-2xl font-bold">{{ __('Co jest na drodze?') }}</p>
            <div class="grid grid-cols-2 gap-3">
                @foreach ($quick as $k => [$ico, $label])
                    <button type="button" data-report="{{ $k }}" class="k19-q flex items-center justify-center gap-2 rounded-2xl bg-white font-display text-xl font-bold text-k19-ink active:scale-95">{{ $ico }} {{ $label }}</button>
                @endforeach
                <button type="button" id="k19-quick-close" class="k19-q rounded-2xl border-2 border-white font-display text-xl font-bold">{{ __('Anuluj') }}</button>
            </div>
        </div>
    </div>

    {{-- Dolny pasek: duże przyciski --}}
    <nav class="z-[500] grid grid-cols-3 gap-2 bg-k19-ink p-2 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
        <a href="{{ route('map.sos') }}" class="flex min-h-16 items-center justify-center rounded-2xl bg-k19-red font-display text-2xl font-extrabold">SOS</a>
        @if ($canReport)
            <button type="button" id="k19-report-btn" class="flex min-h-16 items-center justify-center rounded-2xl bg-[#E8A317] font-display text-xl font-extrabold text-k19-ink">⚠ {{ __('Zgłoś') }}</button>
        @else
            <a href="{{ route('login') }}" class="flex min-h-16 items-center justify-center rounded-2xl bg-[#E8A317] text-center font-display text-lg font-extrabold leading-tight text-k19-ink">{{ __('Zaloguj się, aby zgłaszać') }}</a>
        @endif
        @if ($voice)
            <button type="button" id="k19-ptt" class="flex min-h-16 touch-none select-none flex-col items-center justify-center rounded-2xl bg-white font-display text-xl font-extrabold leading-none text-k19-ink data-[on]:bg-[#14532D] data-[on]:text-white">📻 CB<span id="k19-ptt-sub" class="text-xs font-semibold">{{ __('Dotknij, aby słuchać') }}</span></button>
        @else
            <a href="{{ route('cb.index') }}" class="flex min-h-16 items-center justify-center rounded-2xl bg-white font-display text-xl font-extrabold text-k19-ink">📻 CB</a>
        @endif
    </nav>
</div>

<x-push-toggle dark class="hidden" />
<script>if ('serviceWorker' in navigator) navigator.serviceWorker.register(@js(asset('sw.js'))).catch(() => {});</script>
<script type="module">
(() => {
    const T = @js([
        'ahead' => __('przed Tobą'), 'noGps' => __('Włącz lokalizację, aby korzystać z trybu jazdy.'), 'gps' => __('GPS aktywny'),
        'sent' => __('Dziękujemy! Zgłoszenie jest na mapie.'), 'err' => __('Nie udało się wysłać. Sprawdź dane.'), 'wait' => __('Czekam na pozycję GPS…'),
        'attention' => __('Uwaga'), 'in' => __('za'), 'listen' => __('Dotknij, aby słuchać'), 'hold' => __('Przytrzymaj i mów'), 'talks' => __('Mówi:'),
        'sos' => __('Kierowca potrzebuje pomocy'), 'meters' => __('metrów'), 'kilometers' => __('kilometrów'),
        'border' => __('Witamy w kraju: :c. Pamiętaj o wpisie kraju w tachografie na pierwszym postoju.'),
    ]);
    const lang = @js(app()->getLocale());
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const $ = id => document.getElementById(id);
    const status = (t, ms) => { const s = $('k19-status'); s.textContent = t; s.hidden = false; if (ms) setTimeout(() => s.hidden = true, ms); };

    // Ekran nie gaśnie podczas jazdy
    let lock = null;
    const keepAwake = async () => { try { lock = await navigator.wakeLock?.request('screen'); } catch (e) {} };
    keepAwake(); document.addEventListener('visibilitychange', () => document.visibilityState === 'visible' && keepAwake());

    const map = L.map('k19-map', {zoomControl: false, attributionControl: true}).setView([52.0, 19.4], 7);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);

    // Moja pozycja – strzałka obracana zgodnie z kierunkiem jazdy
    const me = L.marker([0, 0], {icon: L.divIcon({className: '', html: '<div id="k19-me" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;transition:transform .4s"><svg width="44" height="44" viewBox="0 0 44 44"><circle cx="22" cy="22" r="20" fill="#C8221A" stroke="#fff" stroke-width="4"/><path d="M22 9 L31 31 L22 26 L13 31 Z" fill="#fff"/></svg></div>', iconSize: [44, 44], iconAnchor: [22, 22]}), interactive: false});
    let pos = null, heading = null, follow = true, last = null;
    map.on('dragstart', () => follow = false);
    $('k19-center').onclick = () => { follow = true; if (pos) map.setView([pos.lat, pos.lng], Math.max(map.getZoom(), 15)); };

    // Odległość (m) i kierunek (stopnie) między punktami
    const R = 6371000, rad = d => d * Math.PI / 180;
    const dist = (a, b) => { const dl = rad(b.lat - a.lat), dn = rad(b.lng - a.lng); const x = Math.sin(dl / 2) ** 2 + Math.cos(rad(a.lat)) * Math.cos(rad(b.lat)) * Math.sin(dn / 2) ** 2; return 2 * R * Math.asin(Math.sqrt(x)); };
    const bearing = (a, b) => { const y = Math.sin(rad(b.lng - a.lng)) * Math.cos(rad(b.lat)); const x = Math.cos(rad(a.lat)) * Math.sin(rad(b.lat)) - Math.sin(rad(a.lat)) * Math.cos(rad(b.lat)) * Math.cos(rad(b.lng - a.lng)); return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360; };

    // Zgłoszenia innych kierowców
    const icons = {road: '⚠', sos: '🆘', parking: 'P', fuel: '⛽'};
    const probIcons = {jam: '🚗', accident: '💥', police: '👮', roadwork: '🚧', closure: '⛔', low_bridge: '🌉', weight_limit: '⚖', truck_ban: '🚫'};
    const layer = L.layerGroup().addTo(map);
    let reports = [];
    async function loadReports() {
        reports = await fetch(@js(route('map.data')), {headers: {Accept: 'application/json'}}).then(r => r.json()).catch(() => reports);
        layer.clearLayers();
        reports.filter(r => r.kind === 'road' || r.kind === 'sos').forEach(r => {
            const ico = r.kind === 'sos' ? '🆘' : (probIcons[r.data?.problem] || '⚠');
            L.marker([r.lat, r.lng], {icon: L.divIcon({className: '', html: `<span style="display:inline-flex;width:36px;height:36px;align-items:center;justify-content:center;border-radius:10px;background:${r.kind === 'sos' ? '#C8221A' : '#E8A317'};font-size:20px;box-shadow:0 0 0 3px #fff">${ico}</span>`, iconSize: [36, 36], iconAnchor: [18, 18]})}).addTo(layer);
        });
    }
    loadReports(); setInterval(loadReports, 60000);

    // Ostrzeżenia: dźwięk + głos, raz na zgłoszenie
    let sound = true;
    try { sound = localStorage.getItem('k19-sound') !== '0'; } catch (e) {}
    const soundBtn = $('k19-sound'); const paintSound = () => soundBtn.textContent = sound ? '🔊' : '🔇'; paintSound();
    soundBtn.onclick = () => { sound = !sound; paintSound(); try { localStorage.setItem('k19-sound', sound ? '1' : '0'); } catch (e) {} };
    let audio = null;
    function beep() {
        try { audio = audio || new (window.AudioContext || window.webkitAudioContext)(); const o = audio.createOscillator(), g = audio.createGain();
            o.frequency.value = 880; g.gain.value = .25; o.connect(g); g.connect(audio.destination); o.start(); o.stop(audio.currentTime + .25); } catch (e) {}
    }
    function say(text) { try { if (!window.speechSynthesis) return; const u = new SpeechSynthesisUtterance(text); u.lang = lang; speechSynthesis.cancel(); speechSynthesis.speak(u); } catch (e) {} }
    document.addEventListener('pointerdown', () => { try { audio = audio || new (window.AudioContext || window.webkitAudioContext)(); audio.resume(); } catch (e) {} }, {once: true});

    const warned = new Set();
    const fmt = m => m >= 1000 ? (m / 1000).toFixed(1) + ' km' : (Math.round(m / 50) * 50) + ' m';
    function checkAhead() {
        if (!pos) return;
        const speed = pos.speed || 0;
        const range = speed > 20 ? 3000 : 1500; // przy szybkiej jeździe ostrzegamy wcześniej
        let best = null;
        for (const r of reports) {
            if ((r.kind !== 'road' && r.kind !== 'sos') || r.mine) continue;
            const d = dist(pos, r);
            if (d > range) continue;
            if (heading !== null && d > 150) {
                const diff = Math.abs(((bearing(pos, r) - heading) + 540) % 360 - 180);
                if (diff > 50) continue; // nie jest przed nami
            }
            if (!best || d < best.d) best = {r, d};
        }
        const box = $('k19-alert');
        if (!best) { box.hidden = true; return; }
        const label = best.r.kind === 'sos' ? T.sos : best.r.problem_label;
        $('k19-alert-icon').textContent = best.r.kind === 'sos' ? '🆘' : (probIcons[best.r.data?.problem] || '⚠');
        $('k19-alert-text').textContent = label;
        $('k19-alert-sub').textContent = [best.r.data?.road, best.r.data?.note || best.r.message, best.r.confirmations ? '✓ ' + best.r.confirmations : null, T.ahead].filter(Boolean).join(' · ');
        $('k19-alert-dist').textContent = fmt(best.d);
        box.style.background = best.r.kind === 'sos' ? '#C8221A' : '#E8A317';
        box.hidden = false;
        if (!warned.has(best.r.id)) {
            warned.add(best.r.id);
            if (sound) { beep(); say(T.attention + '. ' + label + ', ' + T.in + ' ' + (best.d >= 1000 ? (best.d / 1000).toFixed(1).replace('.0', '') + ' ' + T.kilometers : (Math.round(best.d / 50) * 50) + ' ' + T.meters)); }
            navigator.vibrate?.([200, 100, 200]);
        }
    }

    // Po minięciu utrudnienia pytamy, czy nadal jest
    const canVote = @js($canReport);
    const near = new Map(), asked = new Set();
    let confirmFor = null, confirmTimer = null;
    function checkPassed() {
        if (!canVote || !pos || !$('k19-confirm').hidden) return;
        for (const r of reports) {
            if (r.kind !== 'road' || r.mine || asked.has(r.id)) continue;
            const d = dist(pos, r);
            if (d < 150) near.set(r.id, true);
            else if (d > 250 && near.get(r.id)) { askConfirm(r); break; }
        }
    }
    function askConfirm(r) {
        asked.add(r.id); confirmFor = r;
        $('k19-confirm-text').textContent = r.problem_label;
        $('k19-confirm').hidden = false;
        clearTimeout(confirmTimer); confirmTimer = setTimeout(() => $('k19-confirm').hidden = true, 15000);
    }
    document.querySelectorAll('[data-still]').forEach(b => b.onclick = async () => {
        $('k19-confirm').hidden = true;
        if (!confirmFor) return;
        const r = await fetch(@js(url('/mapa/zgloszenia')) + '/' + confirmFor.id + '/glos', {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({still: b.dataset.still === '1'})});
        const d = await r.json().catch(() => ({}));
        if (r.ok) { status(d.message, 2500); loadReports(); }
    });

    // GPS
    let country = null, countryAt = null, lastPush = 0;
    async function updateCountry() {
        if (!pos || (countryAt && dist(countryAt, pos) < 20000)) return;
        countryAt = {...pos};
        const r = await fetch(@js(route('route.country')) + `?lat=${pos.lat}&lng=${pos.lng}`, {headers: {Accept: 'application/json'}}).then(r => r.json()).catch(() => ({}));
        if (!r.country) return;
        // dziennik krajów (ekran „Do domu”) + przypomnienie o wpisie kraju w tachografie
        let prevCountry = null;
        try {
            const log = JSON.parse(localStorage.getItem('k19-countries') || '{}');
            const d = new Date(), k = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            prevCountry = localStorage.getItem('k19-last-country');
            log[k] = Array.from(new Set([...(log[k] || []), r.country]));
            const cut = new Date(Date.now() - 400 * 864e5).toISOString().slice(0, 10);
            Object.keys(log).forEach(x => { if (x < cut) delete log[x]; });
            localStorage.setItem('k19-countries', JSON.stringify(log));
            localStorage.setItem('k19-last-country', r.country);
        } catch (e) {}
        if (prevCountry && prevCountry !== r.country) {
            let name = r.country;
            try { name = new Intl.DisplayNames([lang], {type: 'region'}).of(r.country) || r.country; } catch (e) {}
            status('📟 ' + T.border.replace(':c', name), 12000);
            if (sound) { beep(); say(T.border.replace(':c', name)); }
        }
        country = r.country;
    }
    if (!navigator.geolocation) status(T.noGps);
    else navigator.geolocation.watchPosition(p => {
        const now = {lat: p.coords.latitude, lng: p.coords.longitude, t: p.timestamp, speed: p.coords.speed};
        if (now.speed == null && last) { const dt = (now.t - last.t) / 1000; if (dt > 0) now.speed = dist(last, now) / dt; }
        if (p.coords.heading != null && !isNaN(p.coords.heading) && (now.speed || 0) > 1.5) heading = p.coords.heading;
        else if (last && dist(last, now) > 15) heading = bearing(last, now);
        if (now.speed != null && now.speed > 70) now.speed = null; // > 250 km/h – błędny odczyt GPS
        last = now; pos = now;
        if (!map.hasLayer(me)) { me.addTo(map); map.setView([pos.lat, pos.lng], 15); }
        if (!$('k19-status').hidden && $('k19-status').dataset.gps !== '1') { $('k19-status').dataset.gps = '1'; status(T.gps, 2000); }
        me.setLatLng([pos.lat, pos.lng]);
        const arrow = document.getElementById('k19-me'); if (arrow && heading !== null) arrow.style.transform = `rotate(${heading}deg)`;
        if (follow) map.panTo([pos.lat, pos.lng], {animate: true});
        $('k19-speed').textContent = now.speed != null ? Math.round(now.speed * 3.6) : '–';
        checkAhead(); checkPassed(); updateCountry(); tacho(now);
        if (!lastPush || now.t - lastPush > 600000) { lastPush = now.t; window.K19PushLocate?.(now.lat, now.lng); }
    }, () => { if (!pos) status(T.noGps); }, {enableHighAccuracy: true, maximumAge: 2000, timeout: 20000});

    // Licznik czasu jazdy (Pauza) – podgląd i automatyczne przełączanie z GPS
    const K = window.K19Tacho;
    let stillSince = null, movingSince = null;
    function tacho(now) {
        if (!K) return;
        const st = K.load();
        const c0 = K.compute(st);
        if (st.auto && now) {
            const v = (now.speed ?? 0) * 3.6;
            if (v > 15) { stillSince = null; movingSince = movingSince ?? now.t; if (!c0.driving && now.t - movingSince > 60000) { K.setActivity(st, 'drive', movingSince); K.save(st); } }
            else if (v < 3) { movingSince = null; stillSince = stillSince ?? now.t; if (c0.driving && now.t - stillSince > 300000) { K.setActivity(st, 'rest', stillSince); K.save(st); } }
        }
        const c = K.compute(st);
        const box = $('k19-tacho');
        $('k19-tacho-left').textContent = c.driving || c.daily > 0 ? K.fmt(c.left) : '4:30';
        box.style.background = c.driving && c.left <= 15 * 60000 ? '#C8221A' : c.driving && c.left <= 45 * 60000 ? '#E8A317' : '';
        box.style.color = c.driving && c.left <= 45 * 60000 && c.left > 15 * 60000 ? '#16181D' : '';
        if (c.driving && c.left <= 15 * 60000 && !warned.has('tacho15')) { warned.add('tacho15'); if (sound) { beep(); say(@js(__('Za 15 minut musisz zrobić przerwę – poszukaj parkingu.'))); } }
    }
    tacho(); setInterval(() => tacho(), 30000);

    // Szybkie zgłoszenia
    const quick = $('k19-quick');
    $('k19-report-btn')?.addEventListener('click', () => quick.hidden = false);
    $('k19-quick-close').onclick = () => quick.hidden = true;
    document.querySelectorAll('[data-report]').forEach(b => b.onclick = async () => {
        quick.hidden = true;
        if (!pos) return status(T.wait, 3000);
        await updateCountry();
        const r = await fetch(@js(route('map.store')), {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json'},
            body: JSON.stringify({kind: 'road', problem: b.dataset.report, lat: +pos.lat.toFixed(6), lng: +pos.lng.toFixed(6), country: country || 'PL'})});
        const d = await r.json().catch(() => ({}));
        status(r.ok ? T.sent : (d.message || T.err), 3000);
        if (r.ok) loadReports();
    });

    // CB: dotknij = słuchaj, przytrzymaj = mów
    const ptt = $('k19-ptt');
    if (ptt) {
        const channels = @js(\App\Models\CbMessage::CHANNELS);
        let live = false, holdTimer = null, talking = false;
        const sub = $('k19-ptt-sub');
        async function join() {
            const ch = channels.includes(country) ? country : 'EU';
            const r = await fetch(@js(route('cb.voice')), {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({channel: ch})});
            const d = await r.json().catch(() => ({}));
            if (!r.ok) throw new Error(d.message);
            await window.K19Voice.connect(d.url, d.token, {onSpeakers: n => sub.textContent = n.length ? T.talks + ' ' + n.join(', ') : T.hold + ' · ' + ch});
            live = true; sub.textContent = T.hold + ' · ' + ch;
        }
        ptt.addEventListener('pointerdown', async e => {
            e.preventDefault();
            if (!live) { try { await join(); } catch (err) { status(err.message || T.err, 3000); } return; }
            holdTimer = setTimeout(async () => { talking = true; ptt.dataset.on = ''; await window.K19Voice.talk(true).catch(() => {}); }, 150);
        });
        const release = async () => { clearTimeout(holdTimer); if (talking) { talking = false; delete ptt.dataset.on; await window.K19Voice.talk(false).catch(() => {}); } };
        ['pointerup', 'pointerleave', 'pointercancel'].forEach(ev => ptt.addEventListener(ev, release));
        ptt.addEventListener('contextmenu', e => e.preventDefault());
    }
})();
</script>
</body>
</html>
