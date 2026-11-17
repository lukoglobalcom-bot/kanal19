@php use App\Http\Controllers\AuthController as A; @endphp
<x-layouts.app :title="__('Kandydaci')">
    <div class="mx-auto max-w-5xl px-4 pt-10 sm:px-6">
        <a href="{{ route('company.offers') }}" class="font-semibold text-k19-red">← {{ __('Moje ogłoszenia') }}</a>
        <h1 class="mt-2 font-display text-[clamp(28px,4vw,40px)] font-extrabold">{{ __('Kandydaci') }}: {{ $offer->title }}</h1>

        <div class="mt-6 flex flex-col gap-3">
            @forelse ($applications as $app)
                @php $p = $app->driver?->driverProfile; @endphp
                <article class="k19-card flex flex-col gap-2 p-5">
                    <div class="flex flex-wrap items-baseline justify-between gap-3">
                        <h2 class="font-display text-xl font-bold">{{ $p ? $p->first_name.' '.$p->last_name : $app->driver?->name }}</h2>
                        <span class="text-sm text-k19-muted">{{ $app->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if ($p)
                        <p class="text-k19-muted">{{ __('Staż:') }} {{ __(A::EXPERIENCE[$p->experience] ?? $p->experience) }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($p->licences ?? [] as $l)<span class="k19-tag">{{ $l }}</span>@endforeach
                            @foreach ($p->vehicles ?? [] as $v)<span class="k19-tag">{{ __(A::VEHICLES[$v] ?? $v) }}</span>@endforeach
                        </div>
                    @endif
                    @if ($app->message)<p class="whitespace-pre-line rounded-xl bg-k19-paper p-3">{{ $app->message }}</p>@endif
                    <p class="font-semibold">{{ __('Telefon:') }} <a href="tel:{{ $app->driver?->phone }}" class="text-k19-red">{{ $app->driver?->phone }}</a></p>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted">{{ __('Nikt jeszcze nie aplikował.') }}</div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
