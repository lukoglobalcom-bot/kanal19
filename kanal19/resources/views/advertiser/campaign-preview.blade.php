@php($L = \App\Support\Languages::ALL)
<x-layouts.app :title="__('Podgląd kampanii')">
    <div class="mx-auto max-w-4xl px-4 pt-10 sm:px-6">
        <a href="{{ route('advertiser.campaigns.edit', $campaign) }}" class="font-semibold text-k19-red">← {{ __('Wróć do edycji') }}</a>
        <h1 class="mt-2 font-display text-[clamp(28px,4vw,40px)] font-extrabold">{{ __('Podgląd kampanii') }}: {{ $campaign->name }}</h1>
        <p class="text-k19-muted">{{ __(\App\Models\AdCampaign::FORMATS[$campaign->format] ?? '') }} · {{ $campaign->target_url }}</p>

        @if ($missing)
            <p class="mt-4 rounded-xl bg-[#FFF1D6] p-3 font-semibold text-[#8A5A00]">{{ __('Brakuje reklamy i artykułu w językach:') }} {{ implode(', ', array_map(fn ($c) => $L[$c] ?? $c, $missing)) }}</p>
        @endif

        @foreach ($campaign->creatives->sortBy(fn ($c) => array_search($c->locale, $campaign->languages)) as $creative)
            <section class="mt-8">
                <h2 class="font-display text-2xl font-extrabold">{{ $L[$creative->locale] ?? $creative->locale }}</h2>
                <div class="mt-3"><x-ad-slot :ad="$creative->setRelation('campaign', $campaign)" /></div>
                <article class="k19-card mt-3 p-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-k19-muted">{{ __('Artykuł sponsorowany') }}</p>
                    <h3 class="mt-1 font-display text-xl font-bold">{{ $creative->article_title }}</h3>
                    <p class="mt-2 line-clamp-4 whitespace-pre-line text-k19-muted">{{ $creative->article_body }}</p>
                </article>
            </section>
        @endforeach

        @if (! $campaign->isAccepted() && $canEdit)
            <form method="POST" action="{{ route('advertiser.campaigns.accept', $campaign) }}" class="k19-card mt-8 flex flex-col gap-4 p-6">
                @csrf
                <label class="flex items-start gap-2.5">
                    <input type="checkbox" name="confirm" value="1" required class="mt-1 size-[18px] accent-k19-red">
                    <span>{{ __('Akceptuję kampanię. Rozumiem, że po akceptacji nie będę mógł jej edytować – zmiany wprowadza tylko administrator.') }}</span>
                </label>
                @error('confirm')<p class="k19-error">{{ $message }}</p>@enderror
                <button class="k19-btn-red self-start" @disabled($missing)>{{ __('Akceptuję kampanię') }}</button>
            </form>
        @endif
    </div>
</x-layouts.app>
