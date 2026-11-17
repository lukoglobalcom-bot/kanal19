<x-layouts.app :title="__('Nowa kampania')">
    <div class="mx-auto max-w-3xl px-4 pt-10 sm:px-6">
        <a href="{{ route('advertiser.index') }}" class="font-semibold text-k19-red">← {{ __('Panel reklamodawcy') }}</a>
        <h1 class="mt-2 font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Nowa kampania') }}</h1>
        <div class="k19-card mt-6 p-6 sm:p-8">
            @include('advertiser.campaign-form', ['campaign' => $campaign])
        </div>
    </div>
</x-layouts.app>
