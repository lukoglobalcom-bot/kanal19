@php
    $canPost = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasVerifiedPhone());
    $default = strtoupper(app()->getLocale()) === 'PL' ? 'PL' : 'EU';
@endphp
<x-layouts.app :title="__('CB Kanał 19')">
    <div class="mx-auto max-w-2xl px-4 pt-8 sm:px-6">
        <x-banner name="N04-cb-radio" fallback="A09-cb-radio" :alt="__('CB Kanał 19')" class="mb-5 max-h-[320px] object-cover" />
        <h1 class="font-display text-5xl font-extrabold">📻 {{ __('CB Kanał 19') }}</h1>
        <p class="mt-1 text-k19-muted">{{ __('Krótkie komunikaty kierowców: korki, kontrole, pogoda, wolne parkingi. Wiadomości znikają po 3 godzinach.') }}</p>

        <div class="mt-5 grid grid-cols-2 gap-3">
            <div>
                <label for="k19-cb-channel" class="k19-label">{{ __('Kanał') }}</label>
                <select id="k19-cb-channel" class="k19-input">
                    @foreach (\App\Models\CbMessage::CHANNELS as $c)
                        <option value="{{ $c }}" @selected($c === $default)>{{ $c === 'EU' ? __('Cała Europa') : $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="k19-cb-road" class="k19-label">{{ __('Droga (opcjonalnie)') }}</label>
                <input id="k19-cb-road" maxlength="12" class="k19-input" placeholder="A2, S8, E40">
            </div>
        </div>

        @if ($canPost && \App\Services\VoiceToken::enabled())
            @vite('resources/js/cb-voice.js')
            <div id="k19-voice" class="k19-card mt-5 flex flex-col items-center gap-3 p-5 text-center">
                <p class="font-display text-2xl font-bold">🎙 {{ __('Głos na kanale') }}</p>
                <p id="k19-voice-state" class="text-sm text-k19-muted">{{ __('Włącz odsłuch, żeby słyszeć kierowców na wybranym kanale.') }}</p>
                <button type="button" id="k19-voice-join" class="k19-btn-dark">{{ __('Włącz odsłuch') }}</button>
                <button type="button" id="k19-voice-ptt" hidden class="flex size-40 select-none items-center justify-center rounded-full bg-k19-red font-display text-3xl font-extrabold text-white shadow-lg touch-none active:scale-95 data-[on]:bg-[#14532D]">{{ __('MÓW') }}</button>
                <p id="k19-voice-who" class="min-h-6 font-semibold text-k19-red" aria-live="polite"></p>
                <p class="text-xs text-k19-muted">{{ __('Przytrzymaj przycisk i mów. Używaj zestawu głośnomówiącego – nie trzymaj telefonu w ręce podczas jazdy.') }}</p>
            </div>
            <script type="module">
            (() => {
                const T = @js(['on' => __('Odsłuch włączony. Na kanale:'), 'off' => __('Wyłącz odsłuch'), 'join' => __('Włącz odsłuch'), 'err' => __('Nie udało się połączyć z głosowym CB.'), 'talks' => __('Mówi:'), 'mic' => __('Brak dostępu do mikrofonu.')]);
                const csrf = document.querySelector('meta[name=csrf-token]').content;
                const ch = document.getElementById('k19-cb-channel');
                const join = document.getElementById('k19-voice-join'), ptt = document.getElementById('k19-voice-ptt');
                const state = document.getElementById('k19-voice-state'), who = document.getElementById('k19-voice-who');
                let live = false;
                async function start() {
                    const r = await fetch(@js(route('cb.voice')), {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({channel: ch.value})});
                    const d = await r.json().catch(() => ({}));
                    if (!r.ok) throw new Error(d.message || T.err);
                    await window.K19Voice.connect(d.url, d.token, {
                        onSpeakers: names => who.textContent = names.length ? T.talks + ' ' + names.join(', ') : '',
                        onState: n => { if (n === null) stop(); else state.textContent = T.on + ' ' + n; },
                    });
                    live = true; join.textContent = T.off; ptt.hidden = false;
                }
                async function stop() { live = false; await window.K19Voice.disconnect(); join.textContent = T.join; ptt.hidden = true; who.textContent = ''; }
                join.onclick = async () => { try { live ? await stop() : await start(); } catch (e) { state.textContent = e.message || T.err; } };
                ch.addEventListener('change', async () => { if (live) { await stop(); try { await start(); } catch (e) { state.textContent = T.err; } } });
                const down = async e => { e.preventDefault(); try { await window.K19Voice.talk(true); ptt.dataset.on = ''; } catch (err) { state.textContent = T.mic; } };
                const up = async () => { delete ptt.dataset.on; await window.K19Voice.talk(false).catch(() => {}); };
                ptt.addEventListener('pointerdown', down);
                ['pointerup', 'pointerleave', 'pointercancel'].forEach(ev => ptt.addEventListener(ev, up));
                ptt.addEventListener('contextmenu', e => e.preventDefault());
            })();
            </script>
        @endif

        <div id="k19-cb-list" class="k19-card mt-5 flex max-h-[60vh] min-h-64 flex-col gap-2 overflow-y-auto p-4" aria-live="polite">
            <p class="text-k19-muted" data-empty>{{ __('Cisza w eterze. Napisz pierwszy komunikat.') }}</p>
        </div>

        @if ($canPost)
            <form id="k19-cb-form" class="mt-4 flex gap-2">
                <label for="k19-cb-body" class="sr-only">{{ __('Wiadomość') }}</label>
                <input id="k19-cb-body" maxlength="280" required class="k19-input flex-1" placeholder="{{ __('np. Korek przed Świeckiem, ok. 20 min') }}">
                <button class="k19-btn-red shrink-0">{{ __('Nadaj') }}</button>
            </form>
            <p id="k19-cb-status" role="status" class="mt-2 hidden text-sm font-semibold text-k19-red"></p>
            <p class="mt-2 text-xs text-k19-muted">{{ __('Nie pisz podczas jazdy. Bez wulgaryzmów i danych osobowych – wiadomości zgłoszone przez 3 osoby znikają.') }}</p>
        @else
            <p class="k19-card mt-4 p-4">
                <a href="{{ route('login') }}" class="font-semibold text-k19-red">{{ __('Zaloguj się') }}</a>,
                {{ __('aby nadawać na CB.') }}
            </p>
        @endif
    </div>

    <script>
    (() => {
        const T = @js([
            'ago' => __('min temu'), 'now' => __('teraz'), 'report' => __('Zgłoś'), 'del' => __('Usuń'),
            'error' => __('Nie udało się wysłać wiadomości.'), 'empty' => __('Cisza w eterze. Napisz pierwszy komunikat.'),
        ]);
        const isAdmin = @js((bool) auth()->user()?->isAdmin());
        const urls = {list: @js(route('cb.messages')), store: @js(auth()->check() ? route('cb.store') : null), base: @js(url('/cb/wiadomosci'))};
        const token = document.querySelector('meta[name=csrf-token]')?.content;
        const list = document.getElementById('k19-cb-list');
        const ch = document.getElementById('k19-cb-channel');
        const road = document.getElementById('k19-cb-road');
        try { const saved = localStorage.getItem('k19-cb-channel'); if (saved) ch.value = saved; } catch (e) {}
        let last = 0;

        const el = (tag, cls, text) => { const n = document.createElement(tag); if (cls) n.className = cls; if (text != null) n.textContent = text; return n; };
        const send = (url, method, body) => fetch(url, {method, headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: body ? JSON.stringify(body) : undefined}).then(r => r.json().catch(() => ({})).then(j => ({ok: r.ok, ...j})));

        function render(m) {
            list.querySelector('[data-empty]')?.remove();
            const row = el('div', 'rounded-xl px-3 py-2 ' + (m.mine ? 'bg-[#FDECEA]' : 'bg-k19-paper'));
            const head = el('div', 'flex flex-wrap items-center gap-2 text-xs text-k19-muted');
            head.append(el('strong', 'text-k19-ink', m.who));
            if (m.road) head.append(el('span', 'rounded bg-k19-ink px-1.5 font-bold text-white', m.road));
            head.append(el('span', '', m.minutes < 1 ? T.now : m.minutes + ' ' + T.ago));
            if (urls.store && !m.mine) {
                const b = el('button', 'ml-auto underline', T.report); b.type = 'button';
                b.onclick = async () => { const r = await send(urls.base + '/' + m.id + '/zglos', 'POST'); if (r.ok) { b.textContent = '✓'; b.disabled = true; } };
                head.append(b);
            }
            if (m.mine || isAdmin) {
                const b = el('button', (urls.store && !m.mine ? '' : 'ml-auto ') + 'underline', T.del); b.type = 'button';
                b.onclick = async () => { const r = await send(urls.base + '/' + m.id, 'DELETE'); if (r.ok) row.remove(); };
                head.append(b);
            }
            row.append(head, el('p', 'mt-0.5 break-words', m.body));
            list.append(row);
        }

        async function load(reset) {
            if (reset) { last = 0; list.replaceChildren(el('p', 'text-k19-muted', T.empty)); list.firstChild.dataset.empty = ''; }
            const q = new URLSearchParams({kanal: ch.value});
            if (road.value.trim()) q.set('droga', road.value.trim());
            if (last) q.set('po', last);
            const res = await fetch(urls.list + '?' + q, {headers: {'Accept': 'application/json'}}).then(r => r.ok ? r.json() : []).catch(() => []);
            const atBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 40;
            res.forEach(m => { render(m); last = Math.max(last, m.id); });
            if (res.length && (reset || atBottom)) list.scrollTop = list.scrollHeight;
        }

        ch.addEventListener('change', () => { try { localStorage.setItem('k19-cb-channel', ch.value); } catch (e) {} load(true); });
        let t; road.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => load(true), 500); });

        const form = document.getElementById('k19-cb-form');
        if (form) form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const body = document.getElementById('k19-cb-body');
            const status = document.getElementById('k19-cb-status');
            const r = await send(urls.store, 'POST', {channel: ch.value, road: road.value.trim() || null, body: body.value});
            if (r.ok) { body.value = ''; status.classList.add('hidden'); load(false); }
            else { status.textContent = r.message || T.error; status.classList.remove('hidden'); }
        });

        load(true);
        setInterval(() => { if (!document.hidden) load(false); }, 8000);
    })();
    </script>
</x-layouts.app>
