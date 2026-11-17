<x-layouts.app :title="__('Panel administratora')">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Panel administratora') }}</h1>

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
