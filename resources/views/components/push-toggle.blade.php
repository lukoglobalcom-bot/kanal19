@props(['dark' => false])
{{-- Włączanie powiadomień SOS w pobliżu (Web Push). Dla zalogowanych z potwierdzonym kontem. --}}
@php($u = auth()->user())
@if ($u && ($u->isAdmin() || $u->hasVerifiedPhone()))
<div {{ $attributes->merge(['class' => 'k19-push flex flex-col gap-2 rounded-2xl p-4 '.($dark ? 'bg-[#23262D] text-white' : 'k19-card')]) }}>
    <div class="flex items-center gap-3">
        <span class="text-3xl">🔔</span>
        <div class="min-w-0 flex-1">
            <p class="font-display text-xl font-bold leading-tight">{{ __('Powiadomienia SOS w pobliżu') }}</p>
            <p class="text-sm {{ $dark ? 'text-[#C9CDD3]' : 'text-k19-muted' }}" data-push-state>{{ __('Dostaniesz powiadomienie, gdy kierowca niedaleko Ciebie wezwie pomoc.') }}</p>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <label class="sr-only" for="push-radius">{{ __('Promień') }}</label>
        <select id="push-radius" data-push-radius class="min-h-11 rounded-xl border border-k19-line bg-white px-3 font-semibold text-k19-ink">
            @foreach ([10, 25, 50, 100, 150] as $km)<option value="{{ $km }}" @selected($km === 50)>{{ $km }} km</option>@endforeach
        </select>
        <button type="button" data-push-btn class="k19-btn-red flex-1">{{ __('Włącz') }}</button>
    </div>
</div>
@once
<script type="module">
(() => {
    const T = @js([
        'on' => __('Włączone – ostrzeżemy Cię o SOS w promieniu'), 'off' => __('Dostaniesz powiadomienie, gdy kierowca niedaleko Ciebie wezwie pomoc.'),
        'enable' => __('Włącz'), 'disable' => __('Wyłącz'), 'denied' => __('Powiadomienia są zablokowane w ustawieniach telefonu.'),
        'ios' => __('Na iPhonie: dodaj Kanał 19 do ekranu początkowego (Udostępnij → Do ekranu początk.), potem włącz powiadomienia.'),
        'err' => __('Nie udało się włączyć powiadomień.'),
    ]);
    const urls = {key: @js(route('push.key')), sub: @js(route('push.subscribe')), loc: @js(route('push.locate'))};
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const send = (url, method, body) => fetch(url, {method, headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify(body)}).then(r => r.json().catch(() => ({})).then(j => ({ok: r.ok, ...j})));
    const b64 = s => { const p = '='.repeat((4 - s.length % 4) % 4); const raw = atob((s + p).replace(/-/g, '+').replace(/_/g, '/')); return Uint8Array.from(raw, c => c.charCodeAt(0)); };
    const here = () => new Promise(res => navigator.geolocation ? navigator.geolocation.getCurrentPosition(p => res({lat: +p.coords.latitude.toFixed(5), lng: +p.coords.longitude.toFixed(5)}), () => res({}), {maximumAge: 300000, timeout: 15000}) : res({}));
    const supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

    document.querySelectorAll('.k19-push').forEach(async box => {
        const btn = box.querySelector('[data-push-btn]'), state = box.querySelector('[data-push-state]'), radius = box.querySelector('[data-push-radius]');
        if (!supported) { state.textContent = T.ios; btn.disabled = true; radius.hidden = true; return; }
        const reg = await navigator.serviceWorker.ready;
        let sub = await reg.pushManager.getSubscription();
        try { const r = localStorage.getItem('k19-push-radius'); if (r) radius.value = r; } catch (e) {}
        const paint = () => { btn.textContent = sub ? T.disable : T.enable; state.textContent = sub ? T.on + ' ' + radius.value + ' km.' : T.off; };
        paint();
        // Odśwież pozycję (bez pytania o zgodę, jeśli już jest)
        if (sub && navigator.permissions) {
            navigator.permissions.query({name: 'geolocation'}).then(async p => { if (p.state === 'granted') { const pos = await here(); if (pos.lat) send(urls.loc, 'POST', {endpoint: sub.endpoint, ...pos}); } }).catch(() => {});
        }
        radius.onchange = async () => { try { localStorage.setItem('k19-push-radius', radius.value); } catch (e) {} if (sub) { await send(urls.sub, 'POST', {...sub.toJSON(), radius_km: +radius.value, ...(await here())}); paint(); } };
        btn.onclick = async () => {
            btn.disabled = true;
            try {
                if (sub) {
                    await send(urls.sub, 'DELETE', {endpoint: sub.endpoint}); await sub.unsubscribe(); sub = null;
                } else {
                    if (await Notification.requestPermission() !== 'granted') { state.textContent = T.denied; return; }
                    const {key} = await fetch(urls.key, {headers: {Accept: 'application/json'}}).then(r => r.json());
                    sub = await reg.pushManager.subscribe({userVisibleOnly: true, applicationServerKey: b64(key)});
                    const r = await send(urls.sub, 'POST', {...sub.toJSON(), radius_km: +radius.value, ...(await here())});
                    if (!r.ok) throw new Error(r.message);
                }
                paint();
            } catch (e) { state.textContent = T.err; } finally { btn.disabled = false; }
        };
    });

    // Tryb jazdy przekazuje pozycję co 10 minut
    window.K19PushLocate = async (lat, lng) => {
        if (!supported) return;
        const reg = await navigator.serviceWorker.getRegistration(); const sub = await reg?.pushManager.getSubscription();
        if (sub) send(urls.loc, 'POST', {endpoint: sub.endpoint, lat: +lat.toFixed(5), lng: +lng.toFixed(5)});
    };
})();
</script>
@endonce
@endif
