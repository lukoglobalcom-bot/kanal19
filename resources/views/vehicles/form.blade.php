@php($v = \App\Models\VehicleListing::class)
<x-layouts.app :title="$listing->exists ? __('Edytuj ogłoszenie') : __('Sprzedaj pojazd')">
    <div class="mx-auto max-w-3xl px-4 pt-10 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ $listing->exists ? __('Edytuj ogłoszenie') : __('Sprzedaj pojazd') }}</h1>
        <p class="text-k19-muted">{{ __('Ogłoszenie jest darmowe i widoczne przez 30 dni. Najpierw zobaczysz podgląd, potem publikujesz.') }}</p>

        <form method="POST" enctype="multipart/form-data" action="{{ $listing->exists ? route('vehicles.update', $listing) : route('vehicles.store') }}" class="k19-card mt-6 flex flex-col gap-5 p-6 sm:p-8">
            @csrf
            @if ($listing->exists) @method('PUT') @endif

            <fieldset>
                <legend class="k19-label">{{ __('Stan pojazdu') }}</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach ($v::CONDITIONS as $k => $l)
                        <label class="k19-pill"><input type="radio" name="condition" value="{{ $k }}" class="sr-only" @checked(old('condition', $listing->condition) === $k)><span>{{ __($l) }}</span></label>
                    @endforeach
                </div>
            </fieldset>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="type" class="k19-label">{{ __('Rodzaj pojazdu') }}</label>
                    <select id="type" name="type" required class="k19-input">
                        @foreach ($v::TYPES as $k => $l)<option value="{{ $k }}" @selected(old('type', $listing->type) === $k)>{{ __($l) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="make" class="k19-label">{{ __('Marka') }}</label>
                    <input id="make" name="make" value="{{ old('make', $listing->make) }}" required maxlength="60" placeholder="Volvo" class="k19-input">
                    @error('make')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="model" class="k19-label">{{ __('Model') }}</label>
                    <input id="model" name="model" value="{{ old('model', $listing->model) }}" required maxlength="80" placeholder="FH 500" class="k19-input">
                    @error('model')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="year" class="k19-label">{{ __('Rocznik') }}</label>
                    <input id="year" name="year" type="number" min="1950" max="{{ date('Y') + 1 }}" value="{{ old('year', $listing->year) }}" class="k19-input">
                </div>
                <div>
                    <label for="mileage" class="k19-label">{{ __('Przebieg (km)') }}</label>
                    <input id="mileage" name="mileage" type="number" min="0" value="{{ old('mileage', $listing->mileage) }}" class="k19-input">
                </div>
                <div>
                    <label for="power_hp" class="k19-label">{{ __('Moc (KM)') }}</label>
                    <input id="power_hp" name="power_hp" type="number" min="0" value="{{ old('power_hp', $listing->power_hp) }}" class="k19-input">
                </div>
                <div>
                    <label for="fuel" class="k19-label">{{ __('Paliwo') }}</label>
                    <select id="fuel" name="fuel" class="k19-input">
                        <option value="">–</option>
                        @foreach ($v::FUELS as $k => $l)<option value="{{ $k }}" @selected(old('fuel', $listing->fuel) === $k)>{{ __($l) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="gearbox" class="k19-label">{{ __('Skrzynia biegów') }}</label>
                    <select id="gearbox" name="gearbox" class="k19-input">
                        <option value="">–</option>
                        @foreach ($v::GEARBOXES as $k => $l)<option value="{{ $k }}" @selected(old('gearbox', $listing->gearbox) === $k)>{{ __($l) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="euro" class="k19-label">{{ __('Norma emisji') }}</label>
                    <select id="euro" name="euro" class="k19-input">
                        <option value="">–</option>
                        @foreach ($v::EURO as $e)<option value="{{ $e }}" @selected(old('euro', $listing->euro) === $e)>{{ strtoupper($e) }}</option>@endforeach
                    </select>
                </div>
            </div>

            <fieldset>
                <legend class="k19-label">{{ __('Cena') }}</legend>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <input name="price" type="number" min="1" required value="{{ old('price', $listing->price) }}" aria-label="{{ __('Cena') }}" class="k19-input">
                    <select name="currency" aria-label="{{ __('Waluta') }}" class="k19-input">
                        @foreach ($v::CURRENCIES as $c)<option @selected(old('currency', $listing->currency) === $c)>{{ $c }}</option>@endforeach
                    </select>
                    <select name="price_type" aria-label="{{ __('Brutto / netto') }}" class="k19-input">
                        @foreach (['netto', 'brutto'] as $t)<option value="{{ $t }}" @selected(old('price_type', $listing->price_type) === $t)>{{ __($t) }}</option>@endforeach
                    </select>
                </div>
                @error('price')<p class="k19-error">{{ $message }}</p>@enderror
                <label class="mt-3 flex items-center gap-2.5"><input type="checkbox" name="vat_invoice" value="1" class="size-[18px] accent-k19-red" @checked(old('vat_invoice', $listing->vat_invoice))>{{ __('Faktura VAT') }}</label>
            </fieldset>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="city" class="k19-label">{{ __('Miejscowość') }}</label>
                    <input id="city" name="city" value="{{ old('city', $listing->city) }}" required class="k19-input">
                    @error('city')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="country" class="k19-label">{{ __('Kraj (kod, np. PL, DE)') }}</label>
                    <input id="country" name="country" value="{{ old('country', $listing->country) }}" required maxlength="2" class="k19-input uppercase">
                </div>
            </div>

            <div>
                <label for="description" class="k19-label">{{ __('Opis') }}</label>
                <textarea id="description" name="description" rows="7" required class="k19-input py-3" placeholder="{{ __('Stan techniczny, wyposażenie, historia serwisowa…') }}">{{ old('description', $listing->description) }}</textarea>
                @error('description')<p class="k19-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="k19-label">{{ __('Telefon dla kupujących') }}</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $listing->phone) }}" required class="k19-input">
                @error('phone')<p class="k19-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="photos" class="k19-label">{{ __('Zdjęcia (maks. 8, JPG/PNG/WEBP do 6 MB)') }}</label>
                @if ($listing->exists && $listing->photos->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-3">
                        @foreach ($listing->photos as $photo)
                            <label class="flex flex-col items-center gap-1 text-sm">
                                <img src="{{ $photo->url() }}" alt="" class="size-24 rounded-lg object-cover">
                                <span class="flex items-center gap-1"><input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" class="accent-k19-red">{{ __('Usuń') }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
                <input id="photos" name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="k19-input py-3">
                @error('photos')<p class="k19-error">{{ $message }}</p>@enderror
                @error('photos.*')<p class="k19-error">{{ $message }}</p>@enderror
            </div>

            <button class="k19-btn-red">{{ $listing->exists ? __('Zapisz zmiany') : __('Zapisz i zobacz podgląd') }}</button>
        </form>
    </div>
</x-layouts.app>
