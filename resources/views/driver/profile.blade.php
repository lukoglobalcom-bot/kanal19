@php use App\Http\Controllers\AuthController as A; $p = $user->driverProfile; @endphp
<x-layouts.app :title="__('Mój profil kierowcy')">
    <div class="mx-auto max-w-5xl px-4 pt-10 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Cześć,') }} {{ $p?->first_name }}!</h1>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <section class="k19-card flex flex-col gap-3 p-6">
                <h2 class="font-display text-xl font-bold">{{ __('Mój profil') }}</h2>
                <p>{{ $p?->first_name }} {{ $p?->last_name }} · {{ $user->phone }} <span class="text-[#1E7A46]">✓ {{ __('potwierdzony') }}</span></p>
                <p class="text-k19-muted">{{ __('Staż:') }} {{ __(A::EXPERIENCE[$p?->experience] ?? '') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($p?->licences ?? [] as $l)<span class="k19-tag">{{ $l }}</span>@endforeach
                    @foreach ($p?->vehicles ?? [] as $v)<span class="k19-tag">{{ __(A::VEHICLES[$v] ?? $v) }}</span>@endforeach
                </div>
            </section>

            <section class="k19-card flex flex-col gap-3 p-6">
                <h2 class="font-display text-xl font-bold">{{ __('Baza kierowców') }}</h2>
                <p class="text-k19-muted">{{ __('Gdy jesteś w bazie, firmy widzą Twoje imię, staż, pojazdy i uprawnienia. Telefon dostaną dopiero, gdy przyjmiesz zaproszenie.') }}</p>
                <form method="POST" action="{{ route('driver.database') }}">
                    @csrf
                    <input type="hidden" name="in_database" value="{{ $p?->in_database ? 0 : 1 }}">
                    <button class="{{ $p?->in_database ? 'k19-btn-ghost' : 'k19-btn-red' }}">
                        {{ $p?->in_database ? __('Usuń mnie z bazy') : __('Dodaj mnie do bazy') }}
                    </button>
                </form>
            </section>
        </div>

        <section class="mt-8">
            <h2 class="font-display text-2xl font-extrabold">{{ __('Zaproszenia od firm') }}</h2>
            <div class="mt-3 flex flex-col gap-3">
                @forelse ($invitations as $inv)
                    <article class="k19-card flex flex-wrap items-center justify-between gap-3 p-5">
                        <div class="min-w-0 flex-[1_1_320px]">
                            <h3 class="font-display text-lg font-bold">{{ $inv->company?->displayName() }}</h3>
                            <p class="whitespace-pre-line text-k19-muted">{{ $inv->message }}</p>
                        </div>
                        @if ($inv->accepted_at)
                            <span class="font-semibold text-[#1E7A46]">✓ {{ __('Przyjęte – firma widzi Twój telefon') }}</span>
                        @else
                            <form method="POST" action="{{ route('driver.invitation.accept', $inv) }}">
                                @csrf
                                <button class="k19-btn-dark min-h-11 text-base">{{ __('Przyjmij i pokaż mój telefon') }}</button>
                            </form>
                        @endif
                    </article>
                @empty
                    <p class="text-k19-muted">{{ __('Brak zaproszeń.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="mt-8">
            <h2 class="font-display text-2xl font-extrabold">{{ __('Moje aplikacje') }}</h2>
            <div class="mt-3 flex flex-col gap-3">
                @forelse ($applications as $app)
                    <a href="{{ route('offers.show', $app->offer) }}" class="k19-card flex flex-wrap justify-between gap-2 p-5 text-k19-ink no-underline hover:border-k19-red">
                        <span class="font-display text-lg font-bold">{{ $app->offer?->title }}</span>
                        <span class="text-k19-muted">{{ $app->offer?->company?->displayName() }} · {{ $app->created_at->format('d.m.Y') }}</span>
                    </a>
                @empty
                    <p class="text-k19-muted">{{ __('Nie aplikowałeś jeszcze na żadną ofertę.') }} <a href="{{ route('home') }}#oferty" class="font-semibold text-k19-red">{{ __('Zobacz oferty') }}</a></p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
