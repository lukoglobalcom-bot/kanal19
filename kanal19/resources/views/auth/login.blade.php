<x-layouts.app :title="__('Zaloguj się')">
    <div class="mx-auto max-w-md px-4 pt-12 sm:px-6">
        <div class="k19-card p-6 sm:p-8">
            <h1 class="font-display text-3xl font-extrabold">{{ __('Zaloguj się') }}</h1>
            <p class="mt-1 text-k19-muted">{{ __('Kierowcy, firmy i reklamodawcy logują się tym samym formularzem.') }}</p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 flex flex-col gap-4">
                @csrf
                <div>
                    <label for="email" class="k19-label">{{ __('E-mail') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="k19-input">
                    @error('email')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="k19-label">{{ __('Hasło') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="k19-input">
                </div>
                <label class="flex items-center gap-2.5"><input type="checkbox" name="remember" value="1" class="size-[18px] accent-k19-red">{{ __('Zapamiętaj mnie') }}</label>
                <button class="k19-btn-red">{{ __('Zaloguj się') }}</button>
            </form>

            <p class="mt-6 text-center text-k19-muted">{{ __('Nie masz konta?') }}
                <a href="{{ route('register', ['konto' => $role]) }}" class="font-semibold text-k19-red">{{ __('Załóż konto za darmo') }}</a>
            </p>
        </div>
    </div>
</x-layouts.app>
