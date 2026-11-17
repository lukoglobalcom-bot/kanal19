@props(['listing'])
@php($v = \App\Models\VehicleListing::class)
<a href="{{ route('vehicles.show', $listing) }}" class="k19-card flex flex-col overflow-hidden text-k19-ink no-underline hover:border-k19-red">
    <div class="relative aspect-[4/3] bg-[#E9E7E1]">
        @if ($cover = $listing->cover())
            <img src="{{ $cover->url() }}" alt="{{ $listing->title() }}" loading="lazy" class="h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center font-display text-lg font-bold text-k19-muted">{{ __('Brak zdjęcia') }}</div>
        @endif
        <span class="absolute left-2 top-2 rounded-md px-2 py-0.5 font-display text-xs font-extrabold uppercase tracking-wide text-white {{ $listing->condition === 'new' ? 'bg-k19-red' : 'bg-k19-ink' }}">{{ __($v::CONDITIONS[$listing->condition] ?? '') }}</span>
    </div>
    <div class="flex flex-1 flex-col gap-1.5 p-4">
        <h3 class="font-display text-lg font-bold leading-snug">{{ $listing->title() }}</h3>
        <p class="text-sm text-k19-muted">
            {{ __($v::TYPES[$listing->type] ?? '') }}
            @if ($listing->mileage) · {{ number_format($listing->mileage, 0, ',', ' ') }} km @endif
            @if ($listing->power_hp) · {{ $listing->power_hp }} KM @endif
        </p>
        <p class="text-sm text-k19-muted">{{ $listing->city }}, {{ $listing->country }}</p>
        <p class="mt-auto pt-2 font-display text-2xl font-extrabold">{{ $listing->priceLabel() }} <span class="text-sm font-semibold text-k19-muted">{{ __($listing->price_type) }}</span></p>
    </div>
</a>
