<x-layouts.app :title="__('Panel reklamodawcy')">
    <div class="mx-auto max-w-4xl px-4 pt-10 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Panel reklamodawcy') }}</h1>
        <div class="k19-card mt-6 flex flex-col gap-3 p-6 sm:p-8">
            <h2 class="font-display text-2xl font-bold">{{ auth()->user()->advertiserProfile?->brand }}</h2>
            <p class="text-lg leading-relaxed">{{ __('Twoje konto jest aktywne. Kampanie reklamowe w wielu językach (reklama + artykuł w każdym języku, podgląd, akceptacja bez edycji) uruchomimy w kolejnym etapie budowy portalu.') }}</p>
            <p class="text-k19-muted">{{ __('Do tego czasu skontaktuj się z nami, żeby zarezerwować miejsce reklamowe.') }}</p>
        </div>
    </div>
</x-layouts.app>
