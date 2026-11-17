<x-layouts.app :title="$offer->title">
    <div class="mx-auto max-w-4xl px-4 pt-10 sm:px-6">
        @include('offers._details', ['offer' => $offer])

        <section class="k19-card mt-6 p-6">
            @guest
                <p class="mb-4 text-lg">{{ __('Zaloguj się jako kierowca, żeby aplikować jednym kliknięciem.') }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="k19-btn-dark">{{ __('Zaloguj się') }}</a>
                    <a href="{{ route('register') }}" class="k19-btn-ghost">{{ __('Załóż konto za darmo') }}</a>
                </div>
            @else
                @if ($applied)
                    <p class="font-display text-xl font-bold text-[#1E7A46]">✓ {{ __('Aplikowałeś na tę ofertę.') }}</p>
                @elseif (auth()->user()->can('apply', $offer))
                    <form method="POST" action="{{ route('offers.apply', $offer) }}" class="flex flex-col gap-4">
                        @csrf
                        <div>
                            <label for="message" class="k19-label">{{ __('Wiadomość do firmy (opcjonalnie)') }}</label>
                            <textarea id="message" name="message" rows="3" class="k19-input py-3">{{ old('message') }}</textarea>
                            @error('message')<p class="k19-error">{{ $message }}</p>@enderror
                        </div>
                        <p class="text-sm text-k19-muted">{{ __('Firma zobaczy Twój profil: staż, uprawnienia, doświadczenie i numer telefonu.') }}</p>
                        <button class="k19-btn-red self-start">{{ __('Aplikuj') }}</button>
                    </form>
                @elseif (! $offer->isActive())
                    <p class="text-k19-muted">{{ __('Ta oferta już wygasła.') }}</p>
                @else
                    <p class="text-k19-muted">{{ __('Aplikować mogą tylko kierowcy z potwierdzonym numerem telefonu.') }}</p>
                @endif
            @endguest
        </section>
    </div>
</x-layouts.app>
