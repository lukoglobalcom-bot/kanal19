<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use App\Models\VehicleListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Strona główna z ofertami, szczegóły oferty i aplikowanie.
 */
class OfferController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'miasto' => ['nullable', 'string', 'max:100'],
            'kategoria' => ['nullable', Rule::in(array_keys(JobOffer::CATEGORIES))],
            'umowa' => ['nullable', 'array'],
            'umowa.*' => [Rule::in(array_keys(JobOffer::CONTRACTS))],
            'tryb' => ['nullable', 'array'],
            'tryb.*' => [Rule::in(array_keys(JobOffer::WORK_MODES))],
            'od' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'bez_cv' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['new', 'pay'])],
        ]);

        $query = JobOffer::active()->with('company.companyProfile');

        if (! empty($filters['q'])) {
            $q = '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['q']).'%';
            $query->where(fn ($w) => $w->where('title', 'like', $q)->orWhere('description', 'like', $q));
        }
        if (! empty($filters['miasto'])) {
            $query->where('city', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['miasto']).'%');
        }
        if (! empty($filters['kategoria'])) {
            $query->where('category', $filters['kategoria']);
        }
        if (! empty($filters['umowa'])) {
            $query->whereIn('contract', $filters['umowa']);
        }
        foreach ($filters['tryb'] ?? [] as $mode) {
            $query->whereJsonContains('work_modes', $mode);
        }
        if (! empty($filters['od'])) {
            $query->where(fn ($w) => $w->where('salary_to', '>=', $filters['od'])->orWhere('salary_from', '>=', $filters['od']));
        }
        if (! empty($filters['bez_cv'])) {
            $query->where('no_cv', true);
        }

        ($filters['sort'] ?? 'new') === 'pay'
            ? $query->orderByDesc('salary_to')->orderByDesc('published_at')
            : $query->orderByDesc('published_at');

        return view('home', [
            'offers' => $query->paginate(15)->withQueryString(),
            'filters' => $filters,
            'vehicles' => VehicleListing::active()->with('photos')->latest('published_at')->limit(4)->get(),
            'billboard' => \App\Models\AdCampaign::pick('billboard'),
            'box' => \App\Models\AdCampaign::pick('box'),
            'posts' => \App\Models\Post::published()->forLocale()->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function show(JobOffer $offer): View
    {
        $user = auth()->user();
        // Szkic widzi tylko jego autor i admin; wygasłą ofertę widzi firma, admin i kierowcy, którzy aplikowali.
        if (! $offer->isActive()) {
            $allowed = $user && ($user->isAdmin() || $offer->user_id === $user->id
                || ($offer->isPublished() && $offer->applications()->where('user_id', $user->id)->exists()));
            abort_unless($allowed, 404);
        }

        return view('offers.show', [
            'offer' => $offer->load('company.companyProfile'),
            'applied' => $user ? $offer->applications()->where('user_id', $user->id)->exists() : false,
        ]);
    }

    public function apply(Request $request, JobOffer $offer): RedirectResponse
    {
        $this->authorize('apply', $offer);

        $data = $request->validate(['message' => ['nullable', 'string', 'max:2000']]);

        $offer->applications()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['message' => $data['message'] ?? null],
        );

        return redirect()->route('offers.show', $offer)->with('status', __('Aplikacja wysłana. Firma zobaczy Twój profil i numer telefonu.'));
    }
}
