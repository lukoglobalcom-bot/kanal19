<x-layouts.app :title="__('Dane operatora portalu')">
    <div class="mx-auto max-w-2xl px-4 pt-10 sm:px-6">
        <a href="{{ route('admin.index') }}" class="text-k19-red">← {{ __('Panel administratora') }}</a>
        <h1 class="mt-2 font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Dane operatora portalu') }}</h1>
        <p class="text-k19-muted">{{ __('Te dane pojawią się w regulaminie i polityce prywatności jako operator i administrator danych.') }}</p>
        <form method="POST" action="{{ route('admin.operator.save') }}" class="k19-card mt-6 flex flex-col gap-4 p-6">
            @csrf
            @foreach ($fields as $key => $label)
                <div>
                    <label for="{{ $key }}" class="k19-label">{{ __($label) }}</label>
                    <input id="{{ $key }}" name="{{ $key }}" value="{{ old($key, \App\Models\Setting::get($key)) }}" class="k19-input" @if (in_array($key, ['operator_name', 'operator_address', 'operator_email'])) required @endif @if ($key === 'operator_email') type="email" @endif>
                    @error($key)<p class="k19-error">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <button class="k19-btn-red">{{ __('Zapisz') }}</button>
        </form>
        <p class="mt-4 flex gap-4"><a href="{{ route('terms') }}" class="text-k19-red">{{ __('Regulamin') }}</a> <a href="{{ route('privacy') }}" class="text-k19-red">{{ __('Polityka prywatności') }}</a></p>
    </div>
</x-layouts.app>
