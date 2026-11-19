<x-layouts.app :title="__('Pauza – czas jazdy')">
    @vite('resources/js/tacho.js')
    <div class="mx-auto max-w-2xl px-4 pt-8 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">☕ {{ __('Pauza – czas jazdy') }}</h1>
        <p class="text-k19-muted">{{ __('Licznik jazdy i odpoczynku według przepisów UE. Dane zostają tylko w Twoim telefonie.') }}</p>

        {{-- Stan --}}
        <section id="t-hero" class="mt-5 rounded-3xl bg-k19-ink p-6 text-white">
            <p class="text-sm font-semibold uppercase tracking-wide text-[#C9CDD3]" id="t-state-label"></p>
            <p class="font-display text-5xl font-extrabold leading-none" id="t-state"></p>
            <p class="mt-1 text-[#C9CDD3]" id="t-since"></p>
            <div class="mt-5 flex flex-wrap items-center gap-2">
                <button type="button" id="t-toggle" class="min-h-16 flex-1 rounded-2xl bg-k19-red px-5 font-display text-2xl font-extrabold"></button>
                <label class="sr-only" for="t-ago">{{ __('Od kiedy') }}</label>
                <select id="t-ago" class="min-h-16 rounded-2xl bg-[#23262D] px-3 font-semibold text-white">
                    <option value="0">{{ __('teraz') }}</option>
                    @foreach ([5, 10, 15, 30, 60] as $m)<option value="{{ $m }}">{{ __(':m min temu', ['m' => $m]) }}</option>@endforeach
                </select>
            </div>
        </section>

        <p id="t-alert" role="alert" hidden class="mt-4 rounded-2xl bg-[#E8A317] p-4 font-display text-xl font-bold text-k19-ink"></p>

        {{-- Liczniki --}}
        <section class="mt-4 grid grid-cols-2 gap-3">
            <div class="k19-card col-span-2 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-k19-muted">{{ __('Do obowiązkowej przerwy') }}</p>
                <p class="font-display text-6xl font-extrabold leading-none" id="t-break"></p>
                <div class="mt-3 h-3 overflow-hidden rounded-full bg-k19-paper"><div id="t-break-bar" class="h-full rounded-full bg-[#14532D] transition-all"></div></div>
                <p class="mt-2 text-sm text-k19-muted" id="t-break-sub"></p>
            </div>
            <div class="k19-card p-4">
                <p class="text-sm font-semibold uppercase tracking-wide text-k19-muted">{{ __('Jazda dziś') }}</p>
                <p class="font-display text-3xl font-extrabold" id="t-daily"></p>
                <p class="text-xs text-k19-muted" id="t-daily-sub"></p>
            </div>
            <div class="k19-card p-4">
                <p class="text-sm font-semibold uppercase tracking-wide text-k19-muted">{{ __('Jazda w tym tygodniu') }}</p>
                <p class="font-display text-3xl font-extrabold" id="t-week"></p>
                <p class="text-xs text-k19-muted" id="t-week-sub"></p>
            </div>
            <p class="col-span-2 rounded-2xl bg-k19-paper p-4 font-semibold" id="t-restby" hidden></p>
        </section>

        {{-- Parkingi w zasięgu --}}
        <section class="k19-card mt-4 p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-display text-2xl font-bold">🅿 {{ __('Parkingi w zasięgu') }}</h2>
                <button type="button" id="t-parkings" class="k19-btn-dark">{{ __('Pokaż') }}</button>
            </div>
            <p class="mt-1 text-sm text-k19-muted">{{ __('Parkingi zgłoszone przez kierowców, do których zdążysz przed limitem (liczymy ok. 70 km/h i drogę dłuższą o 25% od prostej).') }}</p>
            <ul id="t-park-list" class="mt-3 flex flex-col gap-2"></ul>
        </section>

        {{-- Ustawienia i historia --}}
        <section class="k19-card mt-4 flex flex-col gap-3 p-5">
            <label class="flex items-start gap-3"><input type="checkbox" id="t-auto" class="mt-1 size-[20px] accent-k19-red">
                <span><strong>{{ __('Automatycznie z GPS') }}</strong><br><span class="text-sm text-k19-muted">{{ __('W trybie jazdy licznik sam przełączy się na jazdę po ruszeniu i na postój po 5 minutach stania.') }}</span></span></label>
            <h2 class="mt-2 font-display text-xl font-bold">{{ __('Ostatnie 24 godziny') }}</h2>
            <ol id="t-history" class="flex flex-col gap-1 text-sm"></ol>
            <div class="flex flex-wrap gap-2">
                <button type="button" id="t-undo" class="k19-btn-ghost">{{ __('Cofnij ostatnią zmianę') }}</button>
                <button type="button" id="t-reset" class="k19-btn-ghost">{{ __('Wyczyść licznik') }}</button>
            </div>
        </section>

        <p class="mt-4 text-xs text-k19-muted">{{ __('Licznik jest pomocniczy i liczy w uproszczeniu (jazda ciągła 4:30, przerwa 45 min lub 15+30, dziennie 9 h – dwa razy w tygodniu 10 h, tygodniowo 56 h, w dwa tygodnie 90 h). Wiążący jest zawsze tachograf.') }}</p>
    </div>

    <script type="module">
    (() => {
        const T = @js([
            'driving' => __('Jazda'), 'resting' => __('Postój / przerwa'), 'now' => __('Teraz'), 'since' => __('od'),
            'startDrive' => __('Ruszam'), 'startRest' => __('Przerwa / postój'), 'of' => __('z'),
            'ext' => __('wydłużenia do 10 h w tym tygodniu:'), 'twoWeeks' => __('Dwa tygodnie:'),
            'breakRun' => __('Przerwa trwa'), 'breakNeed' => __('do zaliczenia przerwy brakuje'), 'breakOk' => __('Przerwa zaliczona – możesz jechać.'),
            'split' => __('Pierwsza część przerwy (15 min) zaliczona – druga musi mieć min. 30 min.'),
            'restBy' => __('Odpoczynek dzienny zacznij najpóźniej o'), 'limitBreak' => __('przerwa'), 'limitDaily' => __('limit dzienny'), 'limitWeek' => __('limit tygodniowy'),
            'warn15' => __('Za 15 minut musisz zrobić przerwę – poszukaj parkingu.'), 'warn5' => __('Za 5 minut koniec dozwolonej jazdy!'), 'over' => __('Przekroczony czas jazdy – zatrzymaj się na przerwę.'),
            'reach' => __('zdążysz'), 'noReach' => __('nie zdążysz'), 'free' => __('wolnych dla ciężarówek'), 'noPark' => __('Brak zgłoszonych parkingów w okolicy.'), 'noGps' => __('Włącz lokalizację, aby zobaczyć parkingi.'),
            'confirmReset' => __('Wyczyścić cały licznik?'), 'drive' => __('jazda'), 'rest' => __('postój'),
        ]);
        const K = window.K19Tacho;
        let st = K.load();
        const $ = id => document.getElementById(id);
        const hm = ts => new Date(ts).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', hourCycle: 'h23'});
        let warned = {};

        function alertBox(text) { const a = $('t-alert'); a.hidden = !text; a.textContent = text || ''; }
        function warn(key, text) {
            if (warned[key]) return; warned[key] = true;
            navigator.vibrate?.([300, 150, 300]);
            try { const ctx = new (window.AudioContext || window.webkitAudioContext)(); const o = ctx.createOscillator(); o.frequency.value = 880; o.connect(ctx.destination); o.start(); o.stop(ctx.currentTime + .3); } catch (e) {}
            if ('Notification' in window && Notification.permission === 'granted') navigator.serviceWorker?.ready.then(r => r.showNotification('Kanał 19 – ' + T.limitBreak, {body: text, tag: 'k19-tacho', vibrate: [300, 150, 300]})).catch(() => {});
        }

        function render() {
            const c = K.compute(st);
            $('t-state-label').textContent = T.now;
            $('t-state').textContent = c.driving ? T.driving : T.resting;
            $('t-since').textContent = c.since ? T.since + ' ' + hm(c.since) + ' (' + K.fmt(Date.now() - c.since) + ')' : '';
            const tg = $('t-toggle');
            tg.textContent = c.driving ? '☕ ' + T.startRest : '🚛 ' + T.startDrive;
            tg.className = 'min-h-16 flex-1 rounded-2xl px-5 font-display text-2xl font-extrabold ' + (c.driving ? 'bg-[#E8A317] text-k19-ink' : 'bg-k19-red text-white');
            $('t-hero').className = 'mt-5 rounded-3xl p-6 text-white ' + (c.driving ? 'bg-[#14532D]' : 'bg-k19-ink');

            $('t-break').textContent = K.fmt(c.left);
            const pct = Math.min(100, 100 * c.continuous / K.LIMITS.continuous);
            const bar = $('t-break-bar'); bar.style.width = pct + '%'; bar.className = 'h-full rounded-full transition-all ' + (pct > 90 ? 'bg-k19-red' : pct > 70 ? 'bg-[#E8A317]' : 'bg-[#14532D]');
            const lim = {break: T.limitBreak, daily: T.limitDaily, week: T.limitWeek}[c.limitBy];
            $('t-break-sub').textContent = K.fmt(c.continuous) + ' / 4:30 · ' + lim + (c.splitFirst ? ' · ' + T.split : '');

            $('t-daily').textContent = K.fmt(c.daily) + ' / ' + K.fmt(c.dailyLimit);
            $('t-daily-sub').textContent = T.ext + ' ' + Math.max(0, 2 - c.extUsed) + '/2';
            $('t-week').textContent = K.fmt(c.week) + ' / 56:00';
            $('t-week-sub').textContent = T.twoWeeks + ' ' + K.fmt(c.week + c.prevWeek) + ' / 90:00';
            const rb = $('t-restby'); rb.hidden = !c.restBy; if (c.restBy) rb.textContent = '🛏 ' + T.restBy + ' ' + hm(c.restBy);

            if (c.breakInfo) {
                alertBox(c.breakInfo.need > 0 ? '☕ ' + T.breakRun + ' ' + K.fmt(c.breakInfo.elapsed) + ' – ' + T.breakNeed + ' ' + K.fmt(c.breakInfo.need) : (c.continuous === 0 ? '✓ ' + T.breakOk : ''));
                warned = {};
            } else if (c.driving) {
                if (c.left <= 0) { alertBox('⛔ ' + T.over); warn('over', T.over); }
                else if (c.left <= 5 * K.MIN) { alertBox('⚠ ' + T.warn5); warn('w5', T.warn5); }
                else if (c.left <= 15 * K.MIN) { alertBox('⚠ ' + T.warn15); warn('w15', T.warn15); }
                else alertBox('');
            } else alertBox('');

            // historia 24 h
            const list = $('t-history'); list.replaceChildren();
            st.events.filter(e => (e.end ?? Date.now()) > Date.now() - 24 * K.H).slice().reverse().forEach(e => {
                const li = document.createElement('li');
                li.textContent = hm(e.start) + ' – ' + (e.end ? hm(e.end) : '…') + ' · ' + (e.type === 'drive' ? T.drive : T.rest) + ' (' + K.fmt((e.end ?? Date.now()) - e.start) + ')';
                list.append(li);
            });
        }

        $('t-toggle').onclick = () => {
            const c = K.compute(st); const ago = +$('t-ago').value * K.MIN;
            K.setActivity(st, c.driving ? 'rest' : 'drive', Date.now() - ago);
            K.save(st); $('t-ago').value = '0'; render();
            if ('Notification' in window && Notification.permission === 'default') Notification.requestPermission().catch(() => {});
        };
        $('t-undo').onclick = () => { const last = st.events.pop(); const prev = st.events[st.events.length - 1]; if (last && prev && prev.end === last.start) prev.end = null; K.save(st); render(); };
        $('t-reset').onclick = () => { if (confirm(T.confirmReset)) { st = {events: [], auto: st.auto}; K.save(st); render(); } };
        $('t-auto').checked = !!st.auto;
        $('t-auto').onchange = e => { st.auto = e.target.checked; K.save(st); };

        // Parkingi w zasięgu
        $('t-parkings').onclick = () => {
            const ul = $('t-park-list'); ul.replaceChildren();
            if (!navigator.geolocation) return ul.append(Object.assign(document.createElement('li'), {textContent: T.noGps}));
            navigator.geolocation.getCurrentPosition(async p => {
                const me = {lat: p.coords.latitude, lng: p.coords.longitude};
                const data = await fetch(@js(route('map.data')), {headers: {Accept: 'application/json'}}).then(r => r.json()).catch(() => []);
                const left = K.compute(st).left;
                const rad = d => d * Math.PI / 180;
                const km = (a, b) => { const x = Math.sin(rad(b.lat - a.lat) / 2) ** 2 + Math.cos(rad(a.lat)) * Math.cos(rad(b.lat)) * Math.sin(rad(b.lng - a.lng) / 2) ** 2; return 12742 * Math.asin(Math.sqrt(x)); };
                const items = data.filter(r => r.kind === 'parking').map(r => ({r, d: km(me, r) * 1.25})).filter(x => x.d < 250).sort((a, b) => a.d - b.d).slice(0, 12);
                if (!items.length) return ul.append(Object.assign(document.createElement('li'), {textContent: T.noPark, className: 'text-k19-muted'}));
                items.forEach(({r, d}) => {
                    const mins = d / 70 * 60; const ok = mins * K.MIN <= left;
                    const li = document.createElement('li'); li.className = 'flex items-center gap-3 rounded-xl bg-k19-paper p-3';
                    const a = document.createElement('a'); a.href = @js(url('/mapa')) + '?sos=' + r.id + '#k19-map'; a.className = 'min-w-0 flex-1 font-semibold text-k19-ink no-underline';
                    a.textContent = (r.name || 'P') + ' · ' + Math.round(d) + ' km · ~' + K.fmt(mins * K.MIN);
                    const sub = document.createElement('span'); sub.className = 'block text-sm font-normal text-k19-muted';
                    sub.textContent = (r.data?.free_truck != null ? r.data.free_truck + ' ' + T.free : '');
                    a.append(sub);
                    const tag = document.createElement('span'); tag.className = 'shrink-0 rounded-lg px-2 py-1 text-sm font-bold ' + (ok ? 'bg-[#14532D] text-white' : 'bg-k19-red text-white');
                    tag.textContent = ok ? T.reach : T.noReach;
                    li.append(a, tag); ul.append(li);
                });
            }, () => ul.append(Object.assign(document.createElement('li'), {textContent: T.noGps})), {timeout: 15000, maximumAge: 120000});
        };

        render();
        setInterval(render, 15000);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) { st = K.load(); render(); } });
    })();
    </script>
</x-layouts.app>
