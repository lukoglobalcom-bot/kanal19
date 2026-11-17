@php
    use App\Http\Controllers\AuthController as A;
    $tabs = ['driver' => __('Kierowca'), 'company' => __('Firma – dodaję ogłoszenia'), 'advertiser' => __('Reklamodawca')];
    $oldArr = fn ($k) => (array) old($k, []);
@endphp
<x-layouts.app :title="__('Załóż konto')">
    <div class="mx-auto max-w-2xl px-4 pt-12 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Załóż konto za darmo') }}</h1>

        <nav class="mt-5 flex flex-wrap gap-2" aria-label="{{ __('Rodzaj konta') }}">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('register', ['konto' => $key]) }}"
                   class="k19-sign {{ $role === $key ? '' : 'bg-k19-ink opacity-80 hover:opacity-100' }}"
                   @if ($role === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('register') }}" class="k19-card mt-5 flex flex-col gap-5 p-6 sm:p-8">
            @csrf
            <input type="hidden" name="role" value="{{ $role }}">

            @if ($role === 'driver')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="k19-label">{{ __('Imię') }}</label>
                        <input id="first_name" name="first_name" value="{{ old('first_name') }}" required class="k19-input">
                        @error('first_name')<p class="k19-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="last_name" class="k19-label">{{ __('Nazwisko') }}</label>
                        <input id="last_name" name="last_name" value="{{ old('last_name') }}" required class="k19-input">
                        @error('last_name')<p class="k19-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="experience" class="k19-label">{{ __('Staż jako kierowca') }}</label>
                    <select id="experience" name="experience" required class="k19-input">
                        @foreach (A::EXPERIENCE as $k => $l)<option value="{{ $k }}" @selected(old('experience') === $k)>{{ __($l) }}</option>@endforeach
                    </select>
                    @error('experience')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <fieldset>
                    <legend class="k19-label">{{ __('Na czym jeździłeś') }}</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach (A::VEHICLES as $k => $l)
                            <label class="k19-pill"><input type="checkbox" name="vehicles[]" value="{{ $k }}" class="sr-only" @checked(in_array($k, $oldArr('vehicles')))><span>{{ __($l) }}</span></label>
                        @endforeach
                    </div>
                    @error('vehicles')<p class="k19-error">{{ $message }}</p>@enderror
                </fieldset>
                <fieldset>
                    <legend class="k19-label">{{ __('Uprawnienia') }}</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach (A::LICENCES as $l)
                            <label class="k19-pill"><input type="checkbox" name="licences[]" value="{{ $l }}" class="sr-only" @checked(in_array($l, $oldArr('licences')))><span>{{ $l }}</span></label>
                        @endforeach
                    </div>
                    @error('licences')<p class="k19-error">{{ $message }}</p>@enderror
                </fieldset>
                <label class="flex items-start gap-2.5"><input type="checkbox" name="in_database" value="1" class="mt-1 size-[18px] accent-k19-red" @checked(old('in_database'))>
                    <span>{{ __('Dodaj mnie do bazy kierowców. Firmy zobaczą moje imię, staż, pojazdy i uprawnienia (bez telefonu i e-maila) i mogą wysłać mi zaproszenie.') }}</span></label>
            @elseif ($role === 'company')
                <div>
                    <label for="company_name" class="k19-label">{{ __('Nazwa firmy') }}</label>
                    <input id="company_name" name="company_name" value="{{ old('company_name') }}" required class="k19-input">
                    @error('company_name')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nip" class="k19-label">{{ __('NIP') }}</label>
                        <input id="nip" name="nip" value="{{ old('nip') }}" required inputmode="numeric" maxlength="10" class="k19-input">
                        @error('nip')<p class="k19-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="business_type" class="k19-label">{{ __('Rodzaj działalności') }}</label>
                        <select id="business_type" name="business_type" required class="k19-input">
                            @foreach (A::BUSINESS_TYPES as $k => $l)<option value="{{ $k }}" @selected(old('business_type') === $k)>{{ __($l) }}</option>@endforeach
                        </select>
                    </div>
                </div>
            @else
                <div>
                    <label for="brand" class="k19-label">{{ __('Marka / nazwa firmy') }}</label>
                    <input id="brand" name="brand" value="{{ old('brand') }}" required class="k19-input">
                    @error('brand')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nip" class="k19-label">{{ __('NIP') }}</label>
                        <input id="nip" name="nip" value="{{ old('nip') }}" required inputmode="numeric" maxlength="10" class="k19-input">
                        @error('nip')<p class="k19-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="website" class="k19-label">{{ __('Strona www') }}</label>
                        <input id="website" name="website" type="url" value="{{ old('website') }}" placeholder="https://" class="k19-input">
                        @error('website')<p class="k19-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="ad_format" class="k19-label">{{ __('Interesująca forma reklamy') }}</label>
                    <select id="ad_format" name="ad_format" class="k19-input">
                        @foreach (A::AD_FORMATS as $k => $l)<option value="{{ $k }}" @selected(old('ad_format') === $k)>{{ __($l) }}</option>@endforeach
                    </select>
                </div>
            @endif

            <hr class="border-[#E2E0DA]">

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="phone" class="k19-label">{{ __('Telefon (przyjdzie kod SMS)') }}</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required placeholder="+48 600 000 000" autocomplete="tel" class="k19-input">
                    @error('phone')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="locale" class="k19-label">{{ __('Język konta i aplikacji') }}</label>
                    <select id="locale" name="locale" required class="k19-input">
                        @foreach (\App\Support\Languages::ALL as $code => $name)
                            <option value="{{ $code }}" @selected(old('locale', app()->getLocale()) === $code)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="email" class="k19-label">{{ __('E-mail') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="k19-input">
                @error('email')<p class="k19-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="k19-label">{{ __('Hasło (min. 8 znaków)') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="k19-input">
                    @error('password')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="k19-label">{{ __('Powtórz hasło') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="k19-input">
                </div>
            </div>
            <label class="flex items-start gap-2.5"><input type="checkbox" name="terms" value="1" required class="mt-1 size-[18px] accent-k19-red">
                <span>{{ __('Akceptuję regulamin i politykę prywatności.') }}</span></label>
            @error('terms')<p class="k19-error">{{ $message }}</p>@enderror

            <button class="k19-btn-red">{{ __('Załóż konto i wyślij kod SMS') }}</button>
            <p class="text-center text-k19-muted">{{ __('Masz już konto?') }} <a href="{{ route('login') }}" class="font-semibold text-k19-red">{{ __('Zaloguj się') }}</a></p>
        </form>
    </div>
</x-layouts.app>
