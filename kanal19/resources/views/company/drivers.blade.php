@php use App\Http\Controllers\AuthController as A; @endphp
<x-layouts.app :title="__('Baza kierowców')">
    <div class="mx-auto flex max-w-6xl flex-wrap items-start gap-8 px-4 pt-10 sm:px-6">
        <div class="w-full">
            <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Baza kierowców') }}</h1>
            <p class="text-k19-muted">{{ __('Kierowcy, którzy zgodzili się na kontakt i potwierdzili numer SMS-em. Telefon zobaczysz dopiero, gdy kierowca przyjmie zaproszenie.') }}</p>
        </div>

        <form method="GET" class="k19-card flex flex-[1_1_240px] flex-col gap-5 p-5">
            <fieldset>
                <legend class="mb-2 font-display text-lg font-bold">{{ __('Uprawnienia') }}</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach (A::LICENCES as $l)
                        <label class="k19-pill"><input type="checkbox" name="uprawnienia[]" value="{{ $l }}" class="sr-only" @checked(in_array($l, $filters['uprawnienia'] ?? []))><span>{{ $l }}</span></label>
                    @endforeach
                </div>
            </fieldset>
            <fieldset>
                <legend class="mb-2 font-display text-lg font-bold">{{ __('Pojazdy') }}</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach (A::VEHICLES as $k => $l)
                        <label class="k19-pill"><input type="checkbox" name="pojazdy[]" value="{{ $k }}" class="sr-only" @checked(in_array($k, $filters['pojazdy'] ?? []))><span>{{ __($l) }}</span></label>
                    @endforeach
                </div>
            </fieldset>
            <div>
                <label for="staz" class="k19-label font-display text-lg">{{ __('Staż') }}</label>
                <select id="staz" name="staz" class="k19-input">
                    <option value="">{{ __('Dowolny') }}</option>
                    @foreach (A::EXPERIENCE as $k => $l)<option value="{{ $k }}" @selected(($filters['staz'] ?? '') === $k)>{{ __($l) }}</option>@endforeach
                </select>
            </div>
            <button class="k19-btn-red">{{ __('Filtruj') }}</button>
        </form>

        <div class="flex min-w-0 flex-[999_1_560px] flex-col gap-3">
            <p class="text-k19-muted">{{ __('Znaleziono:') }} {{ $drivers->total() }}</p>
            @forelse ($drivers as $p)
                <article class="k19-card flex flex-col gap-3 p-5">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h2 class="font-display text-xl font-bold">{{ $p->first_name }} {{ mb_substr($p->last_name, 0, 1) }}.</h2>
                        <span class="text-k19-muted">{{ __('Staż:') }} {{ __(A::EXPERIENCE[$p->experience] ?? $p->experience) }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($p->licences ?? [] as $l)<span class="k19-tag">{{ $l }}</span>@endforeach
                        @foreach ($p->vehicles ?? [] as $v)<span class="k19-tag">{{ __(A::VEHICLES[$v] ?? $v) }}</span>@endforeach
                    </div>
                    @if ($inv = $invitations->get($p->user_id))
                        @if ($inv->accepted_at)
                            <p class="font-semibold">{{ __('Kierowca przyjął zaproszenie. Telefon:') }} <a href="tel:{{ $p->user?->phone }}" class="text-k19-red">{{ $p->user?->phone }}</a></p>
                        @else
                            <p class="font-semibold text-[#1E7A46]">✓ {{ __('Zaproszenie wysłane – czekamy na odpowiedź kierowcy') }}</p>
                        @endif
                    @else
                        <form method="POST" action="{{ route('company.drivers.invite', $p->user_id) }}" class="flex flex-wrap items-end gap-2">
                            @csrf
                            <div class="flex-[1_1_260px]">
                                <label for="msg-{{ $p->id }}" class="sr-only">{{ __('Wiadomość') }}</label>
                                <input id="msg-{{ $p->id }}" name="message" required maxlength="1000" placeholder="{{ __('Krótka wiadomość, np. trasa i stawka') }}" class="k19-input">
                            </div>
                            <button class="k19-btn-dark min-h-[52px]">{{ __('Zaproś do kontaktu') }}</button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted">{{ __('Brak kierowców spełniających kryteria.') }}</div>
            @endforelse
            {{ $drivers->links() }}
        </div>
    </div>
</x-layouts.app>
