@props(['name', 'fallback' => null, 'alt' => '', 'href' => null])
@php($src = \App\Support\Graphics::banner($name, $fallback))
@if ($src)
    @if ($href)
        <a href="{{ $href }}" {{ $attributes->merge(['class' => 'group block overflow-hidden rounded-2xl no-underline shadow-sm ring-1 ring-black/5']) }}>
            <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" width="1600" height="900" class="block h-auto w-full transition-transform duration-300 group-hover:scale-[1.02]">
        </a>
    @else
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" width="1600" height="900" {{ $attributes->merge(['class' => 'block h-auto w-full rounded-2xl']) }}>
    @endif
@endif
