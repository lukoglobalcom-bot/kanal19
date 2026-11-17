<x-layouts.app :title="__('Aktualności')">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,44px)] font-extrabold">{{ __('Aktualności') }}</h1>
        <p class="text-k19-muted">{{ __('Wiadomości dla kierowców z Polski i Europy, przepisy, mandaty, kontrole ITD, gadżety i nowe pojazdy.') }}</p>

        <nav class="mt-5 flex flex-wrap gap-2" aria-label="{{ __('Kategorie') }}">
            <a href="{{ route('posts.index') }}" class="k19-pill {{ ! $category ? 'border-k19-red bg-k19-red text-white' : '' }}">{{ __('Wszystkie') }}</a>
            @foreach (\App\Models\Post::CATEGORIES as $k => $l)
                <a href="{{ route('posts.index', ['kategoria' => $k]) }}" class="k19-pill {{ $category === $k ? 'border-k19-red bg-k19-red text-white' : '' }}">{{ __($l) }}</a>
            @endforeach
        </nav>

        @if ($posts->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted">{{ __('Brak wpisów w tym języku. Wkrótce pojawią się nowe.') }}</div>
        @else
            <div class="mt-6 grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-4">
                @foreach ($posts as $post)<x-post-card :post="$post" />@endforeach
            </div>
            <div class="mt-6">{{ $posts->links() }}</div>
        @endif
    </div>
</x-layouts.app>
