<x-layouts.app :title="__('Do domu')">
    <div class="mx-auto max-w-2xl px-4 pt-8 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">🏠 {{ __('Do domu') }}</h1>
        <p class="text-k19-muted">{{ __('Dla kierowców, którzy są tygodniami w trasie. Dane zostają tylko w Twoim telefonie.') }}</p>

        {{-- Licznik zjazdu --}}
        <section class="mt-5 rounded-3xl bg-k19-ink p-6 text-white">
            <p class="text-sm font-semibold uppercase tracking-wide text-[#C9CDD3]">{{ __('Zjazd do domu') }}</p>
            <p class="font-display text-6xl font-extrabold leading-none" id="h-days">–</p>
            <p class="mt-1 text-[#C9CDD3]" id="h-sub">{{ __('Ustaw daty wyjazdu i zjazdu poniżej.') }}</p>
            <div class="mt-4 h-3 overflow-hidden rounded-full bg-[#23262D]"><div id="h-bar" class="h-full w-0 rounded-full bg-k19-red transition-all"></div></div>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <label class="text-sm font-semibold text-[#C9CDD3]">{{ __('Wyjazd z domu') }}<input type="date" id="h-from" class="mt-1 min-h-12 w-full rounded-xl bg-white px-3 text-k19-ink"></label>
                <label class="text-sm font-semibold text-[#C9CDD3]">{{ __('Zjazd do domu') }}<input type="date" id="h-to" class="mt-1 min-h-12 w-full rounded-xl bg-white px-3 text-k19-ink"></label>
            </div>
        </section>

        {{-- Wszystko OK --}}
        <section class="k19-card mt-4 flex flex-col gap-3 p-5">
            <h2 class="font-display text-2xl font-bold">💬 {{ __('Daj znać bliskim') }}</h2>
            <p class="text-sm text-k19-muted">{{ __('Jedno dotknięcie – wiadomość z miejscem postoju do wybranej osoby.') }}</p>
            <div class="grid grid-cols-[1fr_auto] gap-2">
                <label class="sr-only" for="h-phone">{{ __('Numer telefonu bliskiej osoby') }}</label>
                <input id="h-phone" type="tel" inputmode="tel" class="k19-input" placeholder="{{ __('Numer telefonu bliskiej osoby') }}" autocomplete="tel">
                <label class="sr-only" for="h-name">{{ __('Imię') }}</label>
                <input id="h-name" class="k19-input w-32" placeholder="{{ __('Imię') }}">
            </div>
            <button type="button" id="h-ok" class="min-h-16 rounded-2xl bg-[#14532D] font-display text-2xl font-extrabold text-white">✓ {{ __('Wszystko OK') }}</button>
            <button type="button" id="h-share" class="k19-btn-ghost">{{ __('Wyślij przez WhatsApp / Messenger…') }}</button>
            <p id="h-msg" role="status" class="hidden text-sm font-semibold"></p>
        </section>

        {{-- Dni w krajach --}}
        <section class="k19-card mt-4 p-5">
            <h2 class="font-display text-2xl font-bold">🌍 {{ __('Dni w krajach') }}</h2>
            <p class="text-sm text-k19-muted">{{ __('Liczone automatycznie w trybie jazdy – możesz też zapisać kraj ręcznie. Przydaje się do rozliczenia diet i delegowania.') }}</p>
            <table class="mt-3 w-full text-left">
                <thead><tr class="text-sm text-k19-muted"><th class="py-1 font-semibold">{{ __('Kraj') }}</th><th class="py-1 text-right font-semibold">{{ __('Dni') }}</th></tr></thead>
                <tbody id="h-countries"></tbody>
            </table>
            <p id="h-none" class="mt-2 text-k19-muted">{{ __('Brak danych z tej trasy.') }}</p>
            <button type="button" id="h-today" class="k19-btn-dark mt-3">📍 {{ __('Zapisz dzisiejszy kraj') }}</button>
        </section>

        <section class="mt-4 rounded-2xl bg-k19-paper p-5">
            <h2 class="font-display text-xl font-bold">📟 {{ __('Tachograf po przekroczeniu granicy') }}</h2>
            <p class="mt-1">{{ __('Po wjeździe do innego kraju wpisz symbol kraju w tachografie na pierwszym możliwym postoju (jeśli tachograf nie robi tego sam). Tryb jazdy przypomni Ci o tym, gdy wykryje zmianę kraju.') }}</p>
        </section>
    </div>

    <script type="module">
    (() => {
        const T = @js([
            'days' => __('dni'), 'today' => __('Zjazd dzisiaj!'), 'past' => __('Zjazd minął – ustaw nowe daty.'), 'of' => __('dzień trasy :d z :t'),
            'ok' => __('Wszystko OK, jestem na postoju:'), 'sent' => __('Otwieramy wiadomość…'), 'noPhone' => __('Wpisz numer telefonu bliskiej osoby.'),
            'noGps' => __('Nie udało się pobrać lokalizacji – wysyłam bez miejsca.'), 'from' => __('wysłane z kanal19.pl'), 'saved' => __('Zapisano:'),
        ]);
        const lang = @js(app()->getLocale());
        const $ = id => document.getElementById(id);
        const get = (k, d) => { try { return JSON.parse(localStorage.getItem(k) || 'null') ?? d; } catch (e) { return d; } };
        const put = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };
        const dayKey = (d = new Date()) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        const names = (() => { try { return new Intl.DisplayNames([lang], {type: 'region'}); } catch (e) { return {of: c => c}; } })();
        const msg = (t, ok = true) => { const m = $('h-msg'); m.textContent = t; m.className = 'text-sm font-semibold ' + (ok ? 'text-[#14532D]' : 'text-k19-red'); };

        // Daty trasy
        const trip = get('k19-trip', {});
        $('h-from').value = trip.from || ''; $('h-to').value = trip.to || '';
        const onDates = () => { put('k19-trip', {from: $('h-from').value, to: $('h-to').value}); render(); };
        $('h-from').onchange = onDates; $('h-to').onchange = onDates;

        function render() {
            const from = $('h-from').value, to = $('h-to').value;
            if (to) {
                const today = new Date(dayKey() + 'T00:00:00'), end = new Date(to + 'T00:00:00');
                const left = Math.round((end - today) / 864e5);
                $('h-days').textContent = left > 0 ? left + ' ' + T.days : left === 0 ? '🏠' : '–';
                $('h-sub').textContent = left === 0 ? T.today : left < 0 ? T.past : (from ? T.of.replace(':d', Math.max(1, Math.round((today - new Date(from + 'T00:00:00')) / 864e5) + 1)).replace(':t', Math.round((end - new Date(from + 'T00:00:00')) / 864e5)) : '');
                if (from) { const all = end - new Date(from + 'T00:00:00'); $('h-bar').style.width = Math.max(0, Math.min(100, 100 * (today - new Date(from + 'T00:00:00')) / all)) + '%'; }
            }
            // Dni w krajach w okresie trasy (albo ostatnie 60 dni)
            const log = get('k19-countries', {});
            const start = from || dayKey(new Date(Date.now() - 60 * 864e5));
            const count = {};
            Object.entries(log).filter(([d]) => d >= start).forEach(([, cs]) => cs.forEach(c => count[c] = (count[c] || 0) + 1));
            const body = $('h-countries'); body.replaceChildren();
            Object.entries(count).sort((a, b) => b[1] - a[1]).forEach(([c, n]) => {
                const tr = document.createElement('tr'); tr.className = 'border-t border-k19-line';
                const a = document.createElement('td'); a.className = 'py-2 font-semibold'; a.textContent = c + ' · ' + (names.of(c) || c);
                const b = document.createElement('td'); b.className = 'py-2 text-right font-display text-2xl font-extrabold'; b.textContent = n;
                tr.append(a, b); body.append(tr);
            });
            $('h-none').hidden = Object.keys(count).length > 0;
        }

        // Kontakt
        const contact = get('k19-home-contact', {});
        $('h-phone').value = contact.phone || ''; $('h-name').value = contact.name || '';
        const saveContact = () => put('k19-home-contact', {phone: $('h-phone').value.trim(), name: $('h-name').value.trim()});
        $('h-phone').onchange = saveContact; $('h-name').onchange = saveContact;

        const position = () => new Promise(res => navigator.geolocation ? navigator.geolocation.getCurrentPosition(p => res(p.coords), () => res(null), {timeout: 12000, maximumAge: 300000}) : res(null));
        async function text() {
            const c = await position();
            if (!c) { msg(T.noGps, false); return (contact.name ? contact.name + ', ' : '') + T.ok.replace(/:$/, '.') + ' – ' + T.from; }
            const link = 'https://www.openstreetmap.org/?mlat=' + c.latitude.toFixed(5) + '&mlon=' + c.longitude.toFixed(5) + '#map=14/' + c.latitude.toFixed(5) + '/' + c.longitude.toFixed(5);
            const name = $('h-name').value.trim();
            return (name ? name + ', ' : '') + T.ok + ' ' + link + ' – ' + T.from;
        }
        $('h-ok').onclick = async () => {
            saveContact();
            const phone = $('h-phone').value.replace(/[^\d+]/g, '');
            if (!phone) return msg(T.noPhone, false);
            const body = await text();
            msg(T.sent);
            const sep = /iPhone|iPad|Mac/.test(navigator.userAgent) ? '&' : '?';
            location.href = 'sms:' + phone + sep + 'body=' + encodeURIComponent(body);
        };
        $('h-share').onclick = async () => {
            const body = await text();
            if (navigator.share) navigator.share({text: body}).catch(() => {});
            else location.href = 'https://wa.me/?text=' + encodeURIComponent(body);
        };

        // Ręczny zapis kraju
        $('h-today').onclick = async () => {
            const c = await position(); if (!c) return msg(T.noGps, false);
            const r = await fetch(@js(route('route.country')) + '?lat=' + c.latitude + '&lng=' + c.longitude, {headers: {Accept: 'application/json'}}).then(r => r.json()).catch(() => ({}));
            if (!r.country) return msg(T.noGps, false);
            const log = get('k19-countries', {}); const k = dayKey();
            log[k] = Array.from(new Set([...(log[k] || []), r.country])); put('k19-countries', log);
            msg(T.saved + ' ' + (names.of(r.country) || r.country)); render();
        };

        render();
    })();
    </script>
</x-layouts.app>
