<x-layouts.app :title="__('Podgląd ogłoszenia')">
    <div class="mx-auto max-w-4xl px-4 pt-10 sm:px-6">
        <div class="mb-5 rounded-2xl border-2 border-dashed border-k19-red bg-white p-4 font-semibold">
            {{ __('To jest podgląd. Tak kierowcy zobaczą Twoje ogłoszenie.') }}
        </div>

        @include('offers._details', ['offer' => $offer])

        <form method="POST" action="{{ route('company.offers.publish', $offer) }}" class="k19-card mt-6 flex flex-col gap-4 p-6">
            @csrf
            <label class="flex items-start gap-2.5">
                <input type="checkbox" name="confirm" value="1" required class="mt-1 size-[18px] accent-k19-red">
                <span>{{ __('Rozumiem, że po publikacji ogłoszenie będzie widoczne przez 30 dni i nie będę mógł go edytować.') }}</span>
            </label>
            @error('confirm')<p class="k19-error">{{ $message }}</p>@enderror
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('company.offers.edit', $offer) }}" class="k19-btn-ghost">{{ __('Wróć do edycji') }}</a>
                <button class="k19-btn-red">{{ __('Publikuj ogłoszenie') }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
