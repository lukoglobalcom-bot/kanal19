{{-- Dolne menu aplikacji na telefonie: Jazda · Zgłoś · CB · Pauza · SOS --}}
@php
    $items = [
        ['drive', route('drive'), __('Jazda'), '<path d="M12 2l7 19-7-4-7 4z"/>'],
        ['report', route('drive', ['zglos' => 1]), __('Zgłoś'), '<path d="M12 3l9 16H3z"/><path d="M12 10v4M12 17v.5"/>'],
        ['cb.index', route('cb.index'), 'CB', '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/>'],
        ['pause', route('pause'), __('Pauza'), '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
    ];
@endphp
<nav aria-label="{{ __('Menu aplikacji') }}" class="fixed inset-x-0 bottom-0 z-[900] grid grid-cols-5 border-t border-[#2E323B] bg-k19-ink px-1 pb-[max(0.4rem,env(safe-area-inset-bottom))] pt-1 md:hidden">
    @foreach ($items as [$name, $href, $label, $icon])
        @php($active = request()->routeIs($name))
        <a href="{{ $href }}" @if ($active) aria-current="page" @endif
           class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-bold no-underline {{ $active ? 'text-k19-red-light' : 'text-[#C9CCD3]' }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
            {{ $label }}
        </a>
    @endforeach
    <a href="{{ route('map.sos') }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-extrabold text-[#FF5A6E] no-underline">
        <span class="flex size-7 items-center justify-center rounded-full bg-k19-red text-[9px] font-extrabold text-white" aria-hidden="true">SOS</span>
        SOS
    </a>
</nav>
