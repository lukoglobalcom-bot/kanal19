@php($stars = fn ($n) => str_repeat('★', (int) round($n)).str_repeat('☆', 5 - (int) round($n)))
<x-layouts.app :title="$company->displayName()" :description="__('Opinie kierowców i oferty pracy:').' '.$company->displayName()">
    <div class="mx-auto max-w-4xl px-4 pt-10 sm:px-6">
        <section class="k19-card flex flex-wrap items-center justify-between gap-6 p-6 sm:p-8">
            <div>
                <p class="font-display text-sm font-extrabold uppercase tracking-widest text-k19-red">{{ __('Pracodawca') }}</p>
                <h1 class="font-display text-[clamp(28px,4vw,40px)] font-extrabold leading-tight">{{ $company->displayName() }}</h1>
                <p class="text-k19-muted">{{ trans_choice('{0} Brak aktywnych ofert|{1} :count aktywna oferta|[2,4] :count aktywne oferty|[5,*] :count aktywnych ofert', $offers->count(), ['count' => $offers->count()]) }}</p>
            </div>
            <div class="text-center">
                @if ($summary)
                    <p class="font-display text-5xl font-extrabold leading-none">{{ number_format($summary['avg'], 1, ',', '') }}</p>
                    <p class="text-2xl text-[#E8A317]" aria-label="{{ $summary['avg'] }} / 5">{{ $stars($summary['avg']) }}</p>
                    <p class="text-sm text-k19-muted">{{ trans_choice('{1} :count opinia|[2,4] :count opinie|[5,*] :count opinii', $summary['count'], ['count' => $summary['count']]) }}</p>
                @else
                    <p class="text-k19-muted">{{ __('Brak opinii – bądź pierwszy.') }}</p>
                @endif
            </div>
        </section>

        @if ($summary)
            <section class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach (\App\Models\CompanyReview::ASPECTS as $key => $label)
                    <div class="k19-card p-4">
                        <p class="text-sm font-semibold text-k19-muted">{{ __($label) }}</p>
                        <p class="font-display text-2xl font-extrabold">{{ $aspects[$key] ? number_format($aspects[$key], 1, ',', '') : '–' }} <span class="text-lg text-[#E8A317]">{{ $aspects[$key] ? $stars($aspects[$key]) : '' }}</span></p>
                    </div>
                @endforeach
            </section>
        @endif

        @if ($offers->isNotEmpty())
            <section class="mt-8">
                <h2 class="font-display text-2xl font-extrabold">{{ __('Oferty pracy') }}</h2>
                <div class="mt-3 flex flex-col gap-3">@foreach ($offers as $offer)<x-offer-card :offer="$offer" />@endforeach</div>
            </section>
        @endif

        {{-- Dodaj opinię --}}
        <section class="mt-8">
            <h2 class="font-display text-2xl font-extrabold">{{ __('Opinie kierowców') }}</h2>
            @if ($canReview)
                <details class="k19-card mt-3 p-5" @if ($errors->any() || ! $mine) open @endif>
                    <summary class="cursor-pointer font-display text-xl font-bold">{{ $mine ? __('Zmień swoją opinię') : __('Dodaj opinię o tej firmie') }}</summary>
                    <form method="POST" action="{{ route('companies.review', $company) }}" class="mt-4 flex flex-col gap-4">
                        @csrf
                        @foreach (['rating' => __('Ocena ogólna')] + collect(\App\Models\CompanyReview::ASPECTS)->map(fn ($l) => __($l))->all() as $field => $label)
                            <fieldset class="flex flex-wrap items-center gap-2">
                                <legend class="k19-label mb-1 w-full">{{ $label }}@if ($field === 'rating') *@endif</legend>
                                @foreach (range(1, 5) as $n)
                                    <label class="k19-pill"><input type="radio" name="{{ $field }}" value="{{ $n }}" class="sr-only" @checked((int) old($field, $mine?->$field) === $n) @if ($field === 'rating') required @endif><span>{{ $n }} ★</span></label>
                                @endforeach
                            </fieldset>
                            @error($field)<p class="k19-error">{{ $message }}</p>@enderror
                        @endforeach
                        <label class="flex items-center gap-2"><input type="checkbox" name="worked_there" value="1" class="size-[18px] accent-k19-red" @checked(old('worked_there', $mine?->worked_there ?? true))>{{ __('Pracowałem / pracuję w tej firmie') }}</label>
                        <div>
                            <label for="comment" class="k19-label">{{ __('Twoja opinia') }} *</label>
                            <textarea id="comment" name="comment" rows="4" maxlength="1000" required class="k19-input py-3" placeholder="{{ __('Jak płacą, jakie auta, jak traktują kierowców? Pisz rzeczowo.') }}">{{ old('comment', $mine?->comment) }}</textarea>
                            @error('comment')<p class="k19-error">{{ $message }}</p>@enderror
                        </div>
                        <label class="flex items-start gap-2"><input type="checkbox" name="rules" value="1" required class="mt-1 size-[18px] accent-k19-red">
                            <span class="text-sm">{{ __('Opisuję własne doświadczenia, piszę prawdę, bez wulgaryzmów i danych osobowych innych osób. Wiem, że firma może odpowiedzieć na opinię.') }}</span></label>
                        @error('rules')<p class="k19-error">{{ $message }}</p>@enderror
                        <button class="k19-btn-red self-start">{{ __('Opublikuj opinię') }}</button>
                    </form>
                </details>
            @elseif (! auth()->check())
                <p class="k19-card mt-3 p-5"><a href="{{ route('login') }}" class="font-semibold text-k19-red">{{ __('Zaloguj się') }}</a> {{ __('jako kierowca, aby dodać opinię.') }}</p>
            @endif

            <div class="mt-4 flex flex-col gap-3">
                @forelse ($reviews as $r)
                    <article class="k19-card p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold">{{ $r->signature() }} @if ($r->worked_there)<span class="k19-tag ml-1">{{ __('pracował w firmie') }}</span>@endif</p>
                            <p class="text-sm text-k19-muted">{{ $r->updated_at->format('d.m.Y') }}</p>
                        </div>
                        <p class="mt-1 text-xl text-[#E8A317]" aria-label="{{ $r->rating }} / 5">{{ $stars($r->rating) }}</p>
                        <p class="mt-2 whitespace-pre-line">{{ $r->comment }}</p>
                        @if ($r->reply)
                            <div class="mt-3 rounded-xl bg-k19-paper p-4">
                                <p class="text-sm font-semibold">{{ __('Odpowiedź firmy') }} · {{ $r->replied_at?->format('d.m.Y') }}</p>
                                <p class="mt-1 whitespace-pre-line">{{ $r->reply }}</p>
                            </div>
                        @endif
                        @auth
                            <div class="mt-3 flex flex-wrap gap-3 text-sm">
                                @if ((int) auth()->id() === (int) $company->id && ! $r->reply)
                                    <details class="w-full">
                                        <summary class="cursor-pointer font-semibold text-k19-red">{{ __('Odpowiedz na opinię') }}</summary>
                                        <form method="POST" action="{{ route('reviews.reply', $r) }}" class="mt-2 flex flex-col gap-2">
                                            @csrf
                                            <label class="sr-only" for="reply-{{ $r->id }}">{{ __('Odpowiedź firmy') }}</label>
                                            <textarea id="reply-{{ $r->id }}" name="reply" rows="3" maxlength="1000" required class="k19-input py-3"></textarea>
                                            <button class="k19-btn-dark self-start">{{ __('Opublikuj odpowiedź') }}</button>
                                        </form>
                                    </details>
                                @endif
                                @if ((int) auth()->id() !== (int) $r->user_id)
                                    <form method="POST" action="{{ route('reviews.report', $r) }}">@csrf<button class="underline">{{ __('Zgłoś') }}</button></form>
                                @endif
                                @if (auth()->user()->isAdmin() || (int) auth()->id() === (int) $r->user_id)
                                    <form method="POST" action="{{ route('reviews.destroy', $r) }}">@csrf @method('DELETE')<button class="underline">{{ __('Usuń') }}</button></form>
                                @endif
                            </div>
                        @endauth
                    </article>
                @empty
                    <p class="text-k19-muted">{{ __('Brak opinii – bądź pierwszy.') }}</p>
                @endforelse
                {{ $reviews->links() }}
            </div>
        </section>
    </div>
</x-layouts.app>
