<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' – ' : '' }}kanal19.pl – {{ __('Portal dla kierowców') }}</title>
    <link rel="icon" href="{{ asset('favicon-32.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#16181D">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-k19-paper font-sans text-k19-ink antialiased">

<header class="bg-k19-ink text-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
        <x-logo />

        <div class="flex flex-wrap items-center gap-2.5">
            <form method="POST" action="{{ route('language') }}" class="flex items-center">
                @csrf
                <label for="lang" class="sr-only">{{ __('Język') }}</label>
                <select id="lang" name="locale" onchange="this.form.submit()"
                        class="min-h-11 rounded-xl border border-[#4A4F5A] bg-k19-ink px-3 font-bold text-white">
                    @foreach (\App\Support\Languages::ALL as $code => $name)
                        <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ strtoupper($code) }} · {{ $name }}</option>
                    @endforeach
                </select>
                <noscript><button class="ml-2 underline">OK</button></noscript>
            </form>

            @auth
                @php($u = auth()->user())
                @if ($u->isCompany())
                    <a href="{{ route('company.offers') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline">{{ __('Moje oferty') }}</a>
                @elseif ($u->isDriver())
                    <a href="{{ route('driver.profile') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline">{{ __('Mój profil') }}</a>
                @elseif ($u->isAdmin())
                    <a href="{{ route('admin.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline">{{ __('Panel admina') }}</a>
                @elseif ($u->isAdvertiser())
                    <a href="{{ route('advertiser.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline">{{ __('Panel reklamodawcy') }}</a>
                @endif
                <a href="{{ route('vehicles.mine') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline">{{ __('Moje pojazdy') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold text-[#C9CCD3] hover:text-white">{{ __('Wyloguj') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline">{{ __('Zaloguj się') }}</a>
                <a href="{{ route('register', ['konto' => 'company']) }}" class="k19-btn-red min-h-11 text-base">{{ __('Dodaj ogłoszenie za darmo') }}</a>
            @endauth
        </div>

        <nav aria-label="{{ __('Główne menu') }}" class="order-3 -mx-4 flex w-[calc(100%+2rem)] flex-nowrap gap-2.5 overflow-x-auto px-4 pb-1 pt-1 [scrollbar-width:none] sm:mx-0 sm:w-full sm:flex-wrap sm:overflow-visible sm:px-0">
            <a href="{{ route('home') }}#oferty" class="k19-sign shrink-0">{{ __('Oferty pracy') }}</a>
            <a href="{{ route('home') }}#kategorie" class="k19-sign shrink-0">{{ __('Kategorie') }}</a>
            <a href="{{ route('vehicles.index') }}" class="k19-sign shrink-0">{{ __('Giełda pojazdów') }}</a>
            <a href="{{ route('map.index') }}" class="k19-sign shrink-0">{{ __('Mapa i parkingi') }}</a>
            <a href="{{ route('cb.index') }}" class="k19-sign shrink-0">{{ __('CB Kanał 19') }}</a>
            <a href="{{ route('route.index') }}" class="k19-sign shrink-0">{{ __('Planer trasy') }}</a>
            <a href="{{ route('posts.index') }}" class="k19-sign shrink-0">{{ __('Aktualności') }}</a>
            @auth
                @if (auth()->user()->isCompany())
                    <a href="{{ route('company.drivers') }}" class="k19-sign shrink-0">{{ __('Baza kierowców') }}</a>
                @endif
            @endauth
            <a href="{{ route('register', ['konto' => 'company']) }}" class="k19-sign shrink-0">{{ __('Dla pracodawców') }}</a>
            <a href="{{ route('vehicles.create') }}" class="k19-sign-sos shrink-0">{{ __('Sprzedaj pojazd') }}</a>
            <a href="{{ route('map.sos') }}" class="k19-sign shrink-0 bg-white text-k19-red">SOS</a>
        </nav>
    </div>
</header>

@if (session('status'))
    <div role="status" class="mx-auto mt-6 max-w-6xl px-4 sm:px-6">
        <div class="rounded-xl border border-[#1E9E5A] bg-[#E8F6EE] px-4 py-3 font-semibold text-[#14532D]">{{ session('status') }}</div>
    </div>
@endif

<main>
    {{ $slot }}
</main>

<footer class="mt-20 bg-k19-ink text-[#C9CCD3]">
    <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 py-12 sm:px-6">
        <div class="flex flex-wrap justify-between gap-10">
            <div class="max-w-sm">
                <x-logo />
                <p class="mt-4 text-[15px] leading-relaxed">{{ __('Portal pracy dla kierowców zawodowych – od busa po ciągnik siodłowy.') }}</p>
            </div>
            <nav aria-label="{{ __('Dla kierowców') }}" class="flex flex-col gap-2.5 text-[15px]">
                <span class="font-bold text-white">{{ __('Dla kierowców') }}</span>
                <a href="{{ route('home') }}#oferty" class="text-[#C9CCD3] no-underline hover:text-white">{{ __('Oferty pracy') }}</a>
                <a href="{{ route('register') }}" class="text-[#C9CCD3] no-underline hover:text-white">{{ __('Załóż konto za darmo') }}</a>
                <a href="{{ route('map.index') }}" class="text-[#C9CCD3] no-underline hover:text-white">{{ __('Mapa i parkingi') }}</a>
                <a href="{{ route('calculator') }}" class="text-[#C9CCD3] no-underline hover:text-white">{{ __('Kalkulator trasy') }}</a>
                <a href="{{ route('app') }}" class="text-[#C9CCD3] no-underline hover:text-white">{{ __('Aplikacja na telefon') }}</a>
            </nav>
            <nav aria-label="{{ __('Dla pracodawców') }}" class="flex flex-col gap-2.5 text-[15px]">
                <span class="font-bold text-white">{{ __('Dla pracodawców') }}</span>
                <a href="{{ route('register', ['konto' => 'company']) }}" class="text-[#C9CCD3] no-underline hover:text-white">{{ __('Dodaj ogłoszenie') }}</a>
                <a href="{{ route('register', ['konto' => 'advertiser']) }}" class="text-[#C9CCD3] no-underline hover:text-white">{{ __('Reklama dla marek') }}</a>
            </nav>
        </div>
        <div class="flex flex-wrap justify-between gap-4 border-t border-[#2E323B] pt-5 text-[13px]">
            <span>© {{ date('Y') }} kanal19.pl</span>
            <span>{{ __('Regulamin') }} · {{ __('Polityka prywatności') }}</span>
        </div>
    </div>
</footer>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {}));
    }
</script>
</body>
</html>
