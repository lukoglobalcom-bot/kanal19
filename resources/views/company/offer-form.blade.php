@php
    use App\Models\JobOffer as O;
    $admin = $admin ?? false;
    $action = $admin ? route('admin.offers.update', $offer) : ($offer->exists ? route('company.offers.update', $offer) : route('company.offers.store'));
    $modes = (array) old('work_modes', $offer->work_modes ?? []);
@endphp
<x-layouts.app :title="$offer->exists ? __('Edytuj ogłoszenie') : __('Nowe ogłoszenie')">
    <div class="mx-auto max-w-3xl px-4 pt-10 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ $offer->exists ? __('Edytuj ogłoszenie') : __('Nowe ogłoszenie') }}</h1>
        <p class="text-k19-muted">
            @if ($admin) {{ __('Edycja administratora – zmiany widać od razu.') }}
            @else {{ __('Najpierw zapisujesz szkic, potem sprawdzasz podgląd i publikujesz. Po publikacji ogłoszenie jest widoczne 30 dni i nie można go edytować.') }} @endif
        </p>

        <form method="POST" action="{{ $action }}" class="k19-card mt-6 flex flex-col gap-5 p-6 sm:p-8">
            @csrf
            @if ($offer->exists) @method('PUT') @endif

            <div>
                <label for="title" class="k19-label">{{ __('Stanowisko') }}</label>
                <input id="title" name="title" value="{{ old('title', $offer->title) }}" required maxlength="120" placeholder="{{ __('np. kierowca C+E chłodnia') }}" class="k19-input">
                @error('title')<p class="k19-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="category" class="k19-label">{{ __('Kategoria') }}</label>
                    <select id="category" name="category" required class="k19-input">
                        @foreach (O::CATEGORIES as $k => $l)<option value="{{ $k }}" @selected(old('category', $offer->category) === $k)>{{ __($l) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="contract" class="k19-label">{{ __('Rodzaj umowy') }}</label>
                    <select id="contract" name="contract" required class="k19-input">
                        @foreach (O::CONTRACTS as $k => $l)<option value="{{ $k }}" @selected(old('contract', $offer->contract) === $k)>{{ __($l) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="city" class="k19-label">{{ __('Miejscowość') }}</label>
                    <input id="city" name="city" value="{{ old('city', $offer->city) }}" required class="k19-input">
                    @error('city')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="country" class="k19-label">{{ __('Kraj (kod, np. PL, DE)') }}</label>
                    <input id="country" name="country" value="{{ old('country', $offer->country) }}" required maxlength="2" class="k19-input uppercase">
                    @error('country')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <fieldset>
                <legend class="k19-label">{{ __('Wynagrodzenie miesięczne') }}</legend>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <input name="salary_from" type="number" min="0" value="{{ old('salary_from', $offer->salary_from) }}" placeholder="{{ __('od') }}" aria-label="{{ __('od') }}" class="k19-input">
                    <input name="salary_to" type="number" min="0" value="{{ old('salary_to', $offer->salary_to) }}" placeholder="{{ __('do') }}" aria-label="{{ __('do') }}" class="k19-input">
                    <select name="currency" aria-label="{{ __('Waluta') }}" class="k19-input">
                        @foreach (O::CURRENCIES as $c)<option @selected(old('currency', $offer->currency) === $c)>{{ $c }}</option>@endforeach
                    </select>
                    <select name="salary_type" aria-label="{{ __('Brutto / netto') }}" class="k19-input">
                        @foreach (['brutto', 'netto'] as $t)<option value="{{ $t }}" @selected(old('salary_type', $offer->salary_type) === $t)>{{ __($t) }}</option>@endforeach
                    </select>
                </div>
                @error('salary_to')<p class="k19-error">{{ $message }}</p>@enderror
            </fieldset>

            <fieldset>
                <legend class="k19-label">{{ __('Tryb pracy') }}</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach (O::WORK_MODES as $k => $l)
                        <label class="k19-pill"><input type="checkbox" name="work_modes[]" value="{{ $k }}" class="sr-only" @checked(in_array($k, $modes))><span>{{ __($l) }}</span></label>
                    @endforeach
                </div>
            </fieldset>

            <div>
                <label for="description" class="k19-label">{{ __('Opis oferty') }}</label>
                <textarea id="description" name="description" rows="7" required class="k19-input py-3">{{ old('description', $offer->description) }}</textarea>
                @error('description')<p class="k19-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="requirements" class="k19-label">{{ __('Wymagania (opcjonalnie)') }}</label>
                <textarea id="requirements" name="requirements" rows="4" class="k19-input py-3">{{ old('requirements', $offer->requirements) }}</textarea>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="language" class="k19-label">{{ __('Język ogłoszenia') }}</label>
                    <select id="language" name="language" class="k19-input">
                        @foreach (\App\Support\Languages::ALL as $code => $name)<option value="{{ $code }}" @selected(old('language', $offer->language) === $code)>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2.5 self-end pb-3"><input type="checkbox" name="no_cv" value="1" class="size-[18px] accent-k19-red" @checked(old('no_cv', $offer->no_cv))>{{ __('Aplikuj bez CV') }}</label>
            </div>

            <button class="k19-btn-red">{{ $admin ? __('Zapisz zmiany') : __('Zapisz szkic i przejdź do podglądu') }}</button>
        </form>
    </div>
</x-layouts.app>
