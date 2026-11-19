@php
    $tiles = [
        ['route' => 'drive', 'icon' => '🚛', 'label' => __('Tryb jazdy'), 'sub' => __('Mapa, ostrzeżenia, CB'), 'class' => 'bg-k19-ink text-white col-span-2 min-h-36'],
        ['route' => 'map.sos', 'icon' => '🆘', 'label' => 'SOS', 'sub' => __('Wezwij pomoc'), 'class' => 'bg-k19-red text-white col-span-2 min-h-36'],
        ['route' => 'map.index', 'icon' => '🅿', 'label' => __('Parkingi'), 'sub' => __('Wolne miejsca'), 'class' => 'bg-white text-k19-ink'],
        ['route' => 'map.index', 'icon' => '⛽', 'label' => __('Paliwo'), 'sub' => __('Ceny od kierowców'), 'class' => 'bg-white text-k19-ink'],
        ['route' => 'route.index', 'icon' => '🗺', 'label' => __('Trasa'), 'sub' => __('Planer dla ciężarówek'), 'class' => 'bg-white text-k19-ink'],
        ['route' => 'cb.index', 'icon' => '📻', 'label' => 'CB', 'sub' => __('Kanał 19 na żywo'), 'class' => 'bg-k19-ink text-white col-span-2'],
        ['route' => 'calculator', 'icon' => '🧮', 'label' => __('Kalkulator trasy'), 'sub' => __('Koszt i pauzy'), 'class' => 'bg-white text-k19-ink'],
        ['route' => 'home', 'icon' => '💼', 'label' => __('Praca'), 'sub' => __('Oferty pracy'), 'class' => 'bg-white text-k19-ink'],
        ['route' => 'vehicles.index', 'icon' => '🚛', 'label' => __('Giełda'), 'sub' => __('Pojazdy'), 'class' => 'bg-white text-k19-ink'],
        ['route' => 'posts.index', 'icon' => '📰', 'label' => __('Aktualności'), 'sub' => __('Przepisy i mandaty'), 'class' => 'bg-white text-k19-ink'],
    ];
@endphp
<x-layouts.app :title="__('Aplikacja')">
    <div class="mx-auto max-w-xl px-4 pt-6 sm:px-6">
        <div class="grid grid-cols-2 gap-3">
            @foreach ($tiles as $t)
                <a href="{{ route($t['route']) }}" class="{{ $t['class'] }} flex min-h-32 flex-col items-center justify-center gap-1 rounded-2xl border-2 border-k19-ink/10 p-3 text-center no-underline shadow-sm active:scale-[.98]">
                    <span class="text-4xl" aria-hidden="true">{{ $t['icon'] }}</span>
                    <span class="font-display text-2xl font-extrabold leading-none">{{ $t['label'] }}</span>
                    <span class="text-sm opacity-80">{{ $t['sub'] }}</span>
                </a>
            @endforeach
        </div>

        <a href="tel:112" class="mt-3 flex min-h-16 items-center justify-center rounded-2xl bg-k19-ink font-display text-2xl font-extrabold text-white no-underline">📞 112</a>

        <div id="k19-install" class="k19-card mt-6 hidden p-5">
            <p class="font-display text-xl font-bold">{{ __('Zainstaluj aplikację Kanał 19') }}</p>
            <p class="mt-1 text-k19-muted">{{ __('Ikona na ekranie telefonu, pełny ekran i szybki dostęp do SOS.') }}</p>
            <button type="button" class="k19-btn-red mt-3">{{ __('Zainstaluj') }}</button>
        </div>
        <p id="k19-ios" class="k19-card mt-6 hidden p-5 text-k19-muted">{{ __('iPhone: w Safari stuknij „Udostępnij”, a potem „Do ekranu początkowego”.') }}</p>
    </div>

    <script>
    (() => {
        let deferred = null;
        const box = document.getElementById('k19-install');
        window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferred = e; box.classList.remove('hidden'); });
        box.querySelector('button').addEventListener('click', async () => { if (!deferred) return; deferred.prompt(); await deferred.userChoice; deferred = null; box.classList.add('hidden'); });
        const ios = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.navigator.standalone;
        if (ios) document.getElementById('k19-ios').classList.remove('hidden');
    })();
    </script>
</x-layouts.app>
