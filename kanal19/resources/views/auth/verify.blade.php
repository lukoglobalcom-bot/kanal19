<x-layouts.app :title="__('Potwierdź numer telefonu')">
    <div class="mx-auto max-w-md px-4 pt-12 sm:px-6">
        <div class="k19-card p-6 sm:p-8">
            <h1 class="font-display text-3xl font-extrabold">{{ __('Potwierdź numer telefonu') }}</h1>
            <p class="mt-2 text-k19-muted">{{ __('Wpisz 6-cyfrowy kod z SMS-a wysłanego na numer') }} <strong class="text-k19-ink">{{ auth()->user()->phone }}</strong>. {{ __('Kod jest ważny 10 minut.') }}</p>

            <form method="POST" action="{{ route('verify') }}" class="mt-6 flex flex-col gap-4">
                @csrf
                <div>
                    <label for="code" class="k19-label">{{ __('Kod SMS') }}</label>
                    <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus
                           class="k19-input text-center font-display text-3xl tracking-[0.4em]">
                    @error('code')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <button class="k19-btn-red">{{ __('Potwierdź') }}</button>
            </form>
            <form method="POST" action="{{ route('verify.resend') }}" class="mt-4 text-center">
                @csrf
                <button class="font-semibold text-k19-red underline">{{ __('Wyślij nowy kod') }}</button>
            </form>
        </div>
    </div>
</x-layouts.app>
