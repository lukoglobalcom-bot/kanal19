<x-layouts.app :title="__('Aktualności')">
    <div class="mx-auto max-w-5xl px-4 pt-10 sm:px-6">
        <a href="{{ route('admin.index') }}" class="font-semibold text-k19-red">← {{ __('Panel administratora') }}</a>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold">{{ __('Aktualności') }}</h1>
            <a href="{{ route('admin.posts.create') }}" class="k19-btn-red">{{ __('Nowy wpis') }}</a>
        </div>
        <div class="k19-card mt-6 overflow-x-auto">
            <table class="w-full min-w-[640px] text-left">
                <thead class="bg-k19-paper text-sm uppercase tracking-wide text-k19-muted">
                    <tr><th class="p-3">{{ __('Tytuł') }}</th><th class="p-3">{{ __('Kategoria') }}</th><th class="p-3">{{ __('Język') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3"></th></tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr class="border-t border-[#E2E0DA]">
                            <td class="p-3 font-semibold"><a href="{{ route('posts.show', $post) }}" class="text-k19-ink">{{ $post->title }}</a></td>
                            <td class="p-3">{{ __(\App\Models\Post::CATEGORIES[$post->category] ?? '') }}</td>
                            <td class="p-3">{{ strtoupper($post->locale) }}</td>
                            <td class="p-3">{{ $post->published_at ? __('Opublikowany') : __('Szkic') }}</td>
                            <td class="flex justify-end gap-2 p-3">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="k19-btn-ghost min-h-10 px-3 text-sm">{{ __('Edytuj') }}</a>
                                <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm(@js(__('Usunąć ten wpis?')))">
                                    @csrf @method('DELETE')
                                    <button class="k19-btn-dark min-h-10 px-3 text-sm">{{ __('Usuń') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-k19-muted">{{ __('Brak wpisów.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $posts->links() }}</div>
    </div>
</x-layouts.app>
