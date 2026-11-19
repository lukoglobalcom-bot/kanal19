<x-layouts.app :title="__('Moje konto')">
    <div class="mx-auto max-w-2xl px-4 pt-10 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Moje konto') }}</h1>

        <section class="k19-card mt-6 p-6">
            <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2">
                <dt class="font-semibold text-k19-muted">{{ __('E-mail') }}</dt><dd class="break-all">{{ $user->email }}</dd>
                <dt class="font-semibold text-k19-muted">{{ __('Telefon') }}</dt><dd>{{ $user->phone ?: '–' }}</dd>
                <dt class="font-semibold text-k19-muted">{{ __('Konto od') }}</dt><dd>{{ $user->created_at?->format('d.m.Y') }}</dd>
            </dl>
            <p class="mt-4 flex flex-wrap gap-4 text-sm">
                <a href="{{ route('terms') }}" class="text-k19-red">{{ __('Regulamin') }}</a>
                <a href="{{ route('privacy') }}" class="text-k19-red">{{ __('Polityka prywatności') }}</a>
            </p>
        </section>

        <x-push-toggle class="mt-6" />

        @unless ($user->isAdmin())
            <section class="mt-6 rounded-2xl border-2 border-k19-red p-6">
                <h2 class="font-display text-2xl font-extrabold text-k19-red">{{ __('Usuń konto') }}</h2>
                <p class="mt-2">{{ __('Usunięcie konta jest nieodwracalne. Usuniemy Twój profil, ogłoszenia, pojazdy, zgłoszenia na mapie, wiadomości CB i powiadomienia.') }}</p>
                <form method="POST" action="{{ route('account.destroy') }}" class="mt-4 flex flex-col gap-3">
                    @csrf @method('DELETE')
                    <label class="k19-label" for="del-pass">{{ __('Hasło') }}</label>
                    <input id="del-pass" type="password" name="password" required autocomplete="current-password" class="k19-input">
                    @error('password')<p class="k19-error">{{ $message }}</p>@enderror
                    <label class="flex items-start gap-2.5"><input type="checkbox" name="confirm" value="1" required class="mt-1 size-[18px] accent-k19-red">
                        <span>{{ __('Rozumiem, że konta i danych nie da się przywrócić.') }}</span></label>
                    @error('confirm')<p class="k19-error">{{ $message }}</p>@enderror
                    <button class="k19-btn bg-k19-red text-white hover:bg-k19-red-dark">{{ __('Usuń konto na zawsze') }}</button>
                </form>
            </section>
        @endunless
    </div>
</x-layouts.app>
