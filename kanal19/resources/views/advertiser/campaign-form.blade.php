{{-- Dane kampanii: nazwa, format, link, języki. Używane przy tworzeniu i na stronie edycji. --}}
@php($langs = (array) old('languages', $campaign->languages ?? []))
<form method="POST" action="{{ $campaign->exists ? route('advertiser.campaigns.update', $campaign) : route('advertiser.campaigns.store') }}" class="flex flex-col gap-5">
    @csrf
    @if ($campaign->exists) @method('PUT') @endif
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="name" class="k19-label">{{ __('Nazwa kampanii') }}</label>
            <input id="name" name="name" value="{{ old('name', $campaign->name) }}" required maxlength="120" class="k19-input">
            @error('name')<p class="k19-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="format" class="k19-label">{{ __('Miejsce reklamy') }}</label>
            <select id="format" name="format" class="k19-input">
                @foreach (\App\Models\AdCampaign::FORMATS as $k => $l)<option value="{{ $k }}" @selected(old('format', $campaign->format) === $k)>{{ __($l) }}</option>@endforeach
            </select>
        </div>
    </div>
    <div>
        <label for="target_url" class="k19-label">{{ __('Adres strony, na którą prowadzi reklama') }}</label>
        <input id="target_url" name="target_url" type="url" value="{{ old('target_url', $campaign->target_url) }}" required placeholder="https://" class="k19-input">
        @error('target_url')<p class="k19-error">{{ $message }}</p>@enderror
    </div>
    <fieldset>
        <legend class="k19-label">{{ __('Języki wyświetlania (w każdym potrzebna reklama i artykuł)') }}</legend>
        <div class="flex flex-wrap gap-2">
            @foreach (\App\Support\Languages::ALL as $code => $name)
                <label class="k19-pill"><input type="checkbox" name="languages[]" value="{{ $code }}" class="sr-only" @checked(in_array($code, $langs))><span>{{ $name }}</span></label>
            @endforeach
        </div>
        @error('languages')<p class="k19-error">{{ $message }}</p>@enderror
    </fieldset>
    <button class="k19-btn-red self-start">{{ $campaign->exists ? __('Zapisz zmiany') : __('Utwórz kampanię') }}</button>
</form>
