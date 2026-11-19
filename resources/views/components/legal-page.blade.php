@props(['title', 'updated', 'other' => null])
<x-layouts.app :title="$title">
    <article class="mx-auto max-w-3xl px-4 pt-8 sm:px-6">
        <h1 class="font-display text-[clamp(32px,4vw,46px)] font-extrabold leading-tight">{{ $title }}</h1>
        <p class="mt-1 text-sm text-k19-muted">{{ app()->getLocale() === 'pl' ? 'Obowiązuje od' : 'Effective from' }}: {{ $updated }}</p>
        @if (app()->getLocale() !== 'pl')
            <p class="mt-4 rounded-xl bg-k19-paper p-4 text-sm">This is a translation for your convenience. The binding version is the Polish one — switch the language to Polski to read it.</p>
        @endif
        <div class="k19-legal mt-6 flex flex-col gap-4 leading-relaxed [&_h2]:mt-6 [&_h2]:font-display [&_h2]:text-2xl [&_h2]:font-bold [&_li]:ml-5 [&_li]:list-disc [&_ol>li]:list-decimal">
            {{ $slot }}
        </div>
    </article>
</x-layouts.app>
