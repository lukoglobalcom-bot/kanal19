@php($L = \App\Support\Languages::ALL)
@php($done = $campaign->creatives->pluck('locale')->all())
<x-layouts.app :title="$campaign->name">
    <div class="mx-auto max-w-4xl px-4 pt-10 sm:px-6">
        <a href="{{ auth()->user()->isAdmin() ? route('admin.index') : route('advertiser.index') }}" class="font-semibold text-k19-red">← {{ __('Wróć') }}</a>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-display text-[clamp(28px,4vw,40px)] font-extrabold">{{ $campaign->name }}</h1>
            <a href="{{ route('advertiser.campaigns.preview', $campaign) }}" class="k19-btn-dark">{{ __('Podgląd i akceptacja') }}</a>
        </div>
        @if ($campaign->isAccepted())
            <p class="mt-2 rounded-xl bg-[#FFF1D6] p-3 font-semibold text-[#8A5A00]">{{ __('Kampania zaakceptowana – edycja tylko przez administratora.') }}</p>
        @endif

        @if ($canEdit)
            <details class="k19-card mt-6 p-5" @if ($errors->hasAny(['name', 'format', 'target_url', 'languages'])) open @endif>
                <summary class="cursor-pointer font-display text-lg font-bold">{{ __('Ustawienia kampanii') }}</summary>
                <div class="mt-4">@include('advertiser.campaign-form', ['campaign' => $campaign])</div>
            </details>
        @endif

        <nav class="mt-6 flex flex-wrap gap-2" aria-label="{{ __('Języki') }}">
            @foreach ($campaign->languages as $code)
                <a href="{{ route('advertiser.campaigns.edit', [$campaign, 'jezyk' => $code]) }}"
                   class="k19-pill {{ $code === $locale ? 'border-k19-red bg-k19-red text-white' : '' }}">
                    {{ in_array($code, $done) ? '✓' : '•' }} {{ $L[$code] ?? $code }}
                </a>
            @endforeach
        </nav>

        <form method="POST" enctype="multipart/form-data" action="{{ route('advertiser.campaigns.creative', [$campaign, $locale]) }}" class="k19-card mt-4 flex flex-col gap-5 p-6 sm:p-8">
            @csrf
            <h2 class="font-display text-2xl font-extrabold">{{ __('Wersja:') }} {{ $L[$locale] ?? $locale }}</h2>
            <fieldset @disabled(! $canEdit) class="flex flex-col gap-5">
                <p class="font-display text-lg font-bold">{{ __('Reklama') }}</p>
                <div>
                    <label for="headline" class="k19-label">{{ __('Nagłówek (do 80 znaków)') }}</label>
                    <input id="headline" name="headline" maxlength="80" required value="{{ old('headline', $creative->headline) }}" class="k19-input">
                    @error('headline')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="text" class="k19-label">{{ __('Tekst reklamy (do 200 znaków)') }}</label>
                    <textarea id="text" name="text" maxlength="200" rows="2" required class="k19-input py-3">{{ old('text', $creative->text) }}</textarea>
                    @error('text')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="image" class="k19-label">{{ __('Obrazek (JPG/PNG/WEBP do 4 MB, opcjonalnie)') }}</label>
                    @if ($creative->imageUrl())<img src="{{ $creative->imageUrl() }}" alt="" class="mb-2 h-24 rounded-lg object-cover">@endif
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="k19-input py-3">
                    @error('image')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <hr class="border-[#E2E0DA]">
                <p class="font-display text-lg font-bold">{{ __('Artykuł') }}</p>
                <div>
                    <label for="article_title" class="k19-label">{{ __('Tytuł artykułu') }}</label>
                    <input id="article_title" name="article_title" maxlength="150" required value="{{ old('article_title', $creative->article_title) }}" class="k19-input">
                    @error('article_title')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="article_body" class="k19-label">{{ __('Treść artykułu (min. 100 znaków)') }}</label>
                    <textarea id="article_body" name="article_body" rows="10" required class="k19-input py-3">{{ old('article_body', $creative->article_body) }}</textarea>
                    @error('article_body')<p class="k19-error">{{ $message }}</p>@enderror
                </div>
                @if ($canEdit)
                    <button class="k19-btn-red self-start">{{ __('Zapisz tę wersję') }}</button>
                @endif
            </fieldset>
        </form>
    </div>
</x-layouts.app>
