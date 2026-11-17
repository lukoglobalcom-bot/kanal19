@props(['tagline' => true, 'dark' => true])
<a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 no-underline {{ $dark ? 'text-white' : 'text-k19-ink' }}" aria-label="kanal19.pl – {{ __('Strona główna') }}">
    <span class="k19-badge h-[34px] w-[50px] text-2xl" aria-hidden="true">19</span>
    <span class="inline-flex flex-col gap-[9px]">
        <span class="font-display text-[28px] font-extrabold leading-none">kanal19<span class="text-k19-red-light">.pl</span></span>
        @if ($tagline)
            <span class="font-display text-[10px] font-semibold uppercase leading-none tracking-[0.28em] text-[#BDBDBD]">{{ __('Portal dla kierowców') }}</span>
        @endif
    </span>
</a>
