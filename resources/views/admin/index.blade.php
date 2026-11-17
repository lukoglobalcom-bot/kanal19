<x-layouts.app :title="__('Panel administratora')">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Panel administratora') }}</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.posts.admin') }}" class="k19-btn-ghost">{{ __('Aktualności') }}</a>
                <a href="{{ route('admin.posts.create') }}" class="k19-btn-red">{{ __('Nowy wpis') }}</a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ($stats as $label => $value)
                <div class="k19-card p-5">
                    <p class="text-sm font-semibold uppercase tracking-wide text-k19-muted">{{ __($label) }}</p>
                    <p class="font-display text-4xl font-extrabold">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <section class="mt-10">
            <h2 class="font-display text-2xl font-extrabold">{{ __('Ogłoszenia') }}</h2>
            <div class="k19-card mt-3 overflow-x-auto">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="bg-k19-paper text-sm uppercase tracking-wide text-k19-muted">
                        <tr><th class="p-3">{{ __('Stanowisko') }}</th><th class="p-3">{{ __('Firma') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3">{{ __('Kandydaci') }}</th><th class="p-3"></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($offers as $offer)
                            <tr class="border-t border-[#E2E0DA]">
                                <td class="p-3 font-semibold"><a href="{{ route('offers.show', $offer) }}" class="text-k19-ink">{{ $offer->title }}</a></td>
                                <td class="p-3">{{ $offer->company?->displayName() }}</td>
                                <td class="p-3">{{ ! $offer->isPublished() ? __('Szkic') : ($offer->isActive() ? __('Aktywne') : __('Wygasło')) }}</td>
                                <td class="p-3">{{ $offer->applications_count }}</td>
                                <td class="flex justify-end gap-2 p-3">
                                    <a href="{{ route('admin.offers.edit', $offer) }}" class="k19-btn-ghost min-h-10 px-3 text-sm">{{ __('Edytuj') }}</a>
                                    <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" onsubmit="return confirm(@js(__('Usunąć to ogłoszenie?')))">
                                        @csrf @method('DELETE')
                                        <button class="k19-btn-dark min-h-10 px-3 text-sm">{{ __('Usuń') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-10">
            <h2 class="font-display text-2xl font-extrabold">{{ __('Kampanie reklamowe') }}</h2>
            <div class="k19-card mt-3 overflow-x-auto">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="bg-k19-paper text-sm uppercase tracking-wide text-k19-muted">
                        <tr><th class="p-3">{{ __('Nazwa') }}</th><th class="p-3">{{ __('Reklamodawca') }}</th><th class="p-3">{{ __('Języki') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($campaigns as $c)
                            <tr class="border-t border-[#E2E0DA]">
                                <td class="p-3 font-semibold">{{ $c->name }}</td>
                                <td class="p-3">{{ $c->advertiser?->displayName() }}</td>
                                <td class="p-3">{{ strtoupper(implode(', ', $c->languages ?? [])) }} ({{ $c->creatives_count }})</td>
                                <td class="p-3">{{ $c->isLive() ? __('Wyświetlana') : ($c->isAccepted() ? __('Czeka na włączenie') : __('Szkic')) }}</td>
                                <td class="flex justify-end gap-2 p-3">
                                    <a href="{{ route('advertiser.campaigns.edit', $c) }}" class="k19-btn-ghost min-h-10 px-3 text-sm">{{ __('Edytuj') }}</a>
                                    @if ($c->isAccepted())
                                        <form method="POST" action="{{ route('admin.campaigns.toggle', $c) }}">
                                            @csrf
                                            <button class="k19-btn-dark min-h-10 px-3 text-sm">{{ $c->active ? __('Wyłącz') : __('Włącz') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-k19-muted">{{ __('Brak kampanii.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-10">
            <h2 class="font-display text-2xl font-extrabold">{{ __('Giełda pojazdów') }}</h2>
            <div class="k19-card mt-3 overflow-x-auto">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="bg-k19-paper text-sm uppercase tracking-wide text-k19-muted">
                        <tr><th class="p-3">{{ __('Pojazd') }}</th><th class="p-3">{{ __('Sprzedający') }}</th><th class="p-3">{{ __('Cena') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($vehicles as $v)
                            <tr class="border-t border-[#E2E0DA]">
                                <td class="p-3 font-semibold"><a href="{{ route('vehicles.show', $v) }}" class="text-k19-ink">{{ $v->title() }}</a></td>
                                <td class="p-3">{{ $v->seller?->name }}</td>
                                <td class="p-3">{{ $v->priceLabel() }}</td>
                                <td class="p-3">{{ ! $v->isPublished() ? __('Szkic') : ($v->isActive() ? __('Aktywne') : __('Wygasło')) }}</td>
                                <td class="flex justify-end gap-2 p-3">
                                    <a href="{{ route('vehicles.edit', $v) }}" class="k19-btn-ghost min-h-10 px-3 text-sm">{{ __('Edytuj') }}</a>
                                    <form method="POST" action="{{ route('vehicles.destroy', $v) }}" onsubmit="return confirm(@js(__('Usunąć to ogłoszenie?')))">
                                        @csrf @method('DELETE')
                                        <button class="k19-btn-dark min-h-10 px-3 text-sm">{{ __('Usuń') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-k19-muted">{{ __('Brak ogłoszeń.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-10">
            <h2 class="font-display text-2xl font-extrabold">{{ __('Użytkownicy') }}</h2>
            <div class="k19-card mt-3 overflow-x-auto">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="bg-k19-paper text-sm uppercase tracking-wide text-k19-muted">
                        <tr><th class="p-3">{{ __('Nazwa') }}</th><th class="p-3">{{ __('E-mail') }}</th><th class="p-3">{{ __('Rola') }}</th><th class="p-3">{{ __('Telefon') }}</th><th class="p-3"></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr class="border-t border-[#E2E0DA] {{ $u->isBlocked() ? 'opacity-60' : '' }}">
                                <td class="p-3 font-semibold">{{ $u->name }}</td>
                                <td class="p-3">{{ $u->email }}</td>
                                <td class="p-3">{{ __(['driver' => 'Kierowca', 'company' => 'Firma', 'advertiser' => 'Reklamodawca'][$u->role] ?? $u->role) }}</td>
                                <td class="p-3">{{ $u->phone }} @if ($u->hasVerifiedPhone())<span class="text-[#1E7A46]">✓</span>@endif</td>
                                <td class="p-3 text-right">
                                    <form method="POST" action="{{ route('admin.users.block', $u) }}">
                                        @csrf
                                        <button class="k19-btn-ghost min-h-10 px-3 text-sm">{{ $u->isBlocked() ? __('Odblokuj') : __('Zablokuj') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
