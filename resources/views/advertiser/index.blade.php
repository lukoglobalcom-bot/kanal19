<x-layouts.app :title="__('Panel reklamodawcy')">
    <div class="mx-auto max-w-5xl px-4 pt-10 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Panel reklamodawcy') }}</h1>
                <p class="text-k19-muted">{{ auth()->user()->advertiserProfile?->brand }}</p>
            </div>
            <a href="{{ route('advertiser.campaigns.create') }}" class="k19-btn-red">{{ __('Nowa kampania') }}</a>
        </div>

        <div class="k19-card mt-6 p-5 text-[15px] leading-relaxed">
            <p class="font-display text-lg font-bold">{{ __('Jak to działa') }}</p>
            <ol class="mt-2 list-decimal space-y-1 pl-5 text-k19-muted">
                <li>{{ __('Wybierz języki, w których ma się wyświetlać reklama.') }}</li>
                <li>{{ __('W każdym języku dodaj jedną reklamę i jeden artykuł.') }}</li>
                <li>{{ __('Sprawdź podgląd i zaakceptuj. Po akceptacji kampanii nie można edytować – zmiany wprowadza tylko administrator.') }}</li>
                <li>{{ __('Administrator włącza kampanię i od tej chwili widzą ją użytkownicy w wybranych językach.') }}</li>
            </ol>
        </div>

        <div class="mt-6 flex flex-col gap-3">
            @forelse ($campaigns as $c)
                <article class="k19-card flex flex-wrap items-center justify-between gap-4 p-5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md px-2 py-0.5 text-sm font-bold {{ $c->isLive() ? 'bg-[#DDF3E6] text-[#1E7A46]' : ($c->isAccepted() ? 'bg-[#FFF1D6] text-[#8A5A00]' : 'bg-[#ECEBE7] text-k19-muted') }}">
                                {{ $c->isLive() ? __('Wyświetlana') : ($c->isAccepted() ? __('Czeka na włączenie') : __('Szkic')) }}
                            </span>
                            <span class="text-sm text-k19-muted">{{ __(\App\Models\AdCampaign::FORMATS[$c->format] ?? $c->format) }}</span>
                        </div>
                        <h2 class="mt-1 font-display text-xl font-bold">{{ $c->name }}</h2>
                        <p class="text-sm text-k19-muted">{{ __('Języki:') }} {{ strtoupper(implode(', ', $c->languages ?? [])) }} · {{ __('Gotowe:') }} {{ $c->creatives_count }}/{{ count($c->languages ?? []) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('advertiser.campaigns.preview', $c) }}" class="k19-btn-ghost min-h-11 text-base">{{ __('Podgląd') }}</a>
                        @unless ($c->isAccepted())
                            <a href="{{ route('advertiser.campaigns.edit', $c) }}" class="k19-btn-dark min-h-11 text-base">{{ __('Edytuj') }}</a>
                        @endunless
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted">{{ __('Nie masz jeszcze kampanii.') }}</div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
