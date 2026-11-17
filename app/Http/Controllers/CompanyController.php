<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobOfferRequest;
use App\Models\ContactInvitation;
use App\Models\DriverProfile;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Panel firmy: dodawanie ofert (szkic → podgląd → publikacja), kandydaci, baza kierowców.
 */
class CompanyController extends Controller
{
    public function offers(Request $request): View
    {
        return view('company.offers', [
            'offers' => $request->user()->jobOffers()->withCount('applications')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', JobOffer::class);

        return view('company.offer-form', ['offer' => new JobOffer(['country' => 'PL', 'currency' => 'PLN', 'salary_type' => 'brutto', 'language' => app()->getLocale()])]);
    }

    /** Krok 1: zapis szkicu i przejście do podglądu. */
    public function store(JobOfferRequest $request): RedirectResponse
    {
        $this->authorize('create', JobOffer::class);

        $offer = $request->user()->jobOffers()->create($request->validated());

        return redirect()->route('company.offers.preview', $offer);
    }

    public function edit(Request $request, JobOffer $offer): View
    {
        $this->authorize('updateDraft', $offer);

        return view('company.offer-form', ['offer' => $offer]);
    }

    public function update(JobOfferRequest $request, JobOffer $offer): RedirectResponse
    {
        $this->authorize('updateDraft', $offer);

        $offer->update($request->validated());

        return redirect()->route('company.offers.preview', $offer);
    }

    /** Krok 2: podgląd dokładnie tak, jak zobaczy go kierowca. */
    public function preview(JobOffer $offer): View
    {
        $this->authorize('updateDraft', $offer);

        return view('company.offer-preview', ['offer' => $offer->load('company.companyProfile')]);
    }

    /** Krok 3: publikacja – od tej chwili 30 dni i bez edycji. */
    public function publish(Request $request, JobOffer $offer): RedirectResponse
    {
        $this->authorize('updateDraft', $offer);
        $request->validate(['confirm' => ['accepted']]);

        $offer->publish();

        return redirect()->route('company.offers')->with('status', __('Oferta opublikowana. Będzie widoczna przez 30 dni.'));
    }

    public function applications(JobOffer $offer): View
    {
        $this->authorize('viewApplications', $offer);

        return view('company.applications', [
            'offer' => $offer,
            'applications' => $offer->applications()->with('driver.driverProfile')->latest()->get(),
        ]);
    }

    /** Baza kierowców: tylko kierowcy ze zgodą i potwierdzonym numerem. Bez telefonu i e-maila. */
    public function drivers(Request $request): View
    {
        $filters = $request->validate([
            'uprawnienia' => ['nullable', 'array'],
            'uprawnienia.*' => [Rule::in(AuthController::LICENCES)],
            'pojazdy' => ['nullable', 'array'],
            'pojazdy.*' => [Rule::in(array_keys(AuthController::VEHICLES))],
            'staz' => ['nullable', Rule::in(array_keys(AuthController::EXPERIENCE))],
        ]);

        $query = DriverProfile::query()
            ->where('in_database', true)
            ->whereHas('user', fn ($u) => $u->whereNotNull('phone_verified_at')->whereNull('blocked_at'));

        foreach ($filters['uprawnienia'] ?? [] as $l) {
            $query->whereJsonContains('licences', $l);
        }
        foreach ($filters['pojazdy'] ?? [] as $v) {
            $query->whereJsonContains('vehicles', $v);
        }
        if (! empty($filters['staz'])) {
            $query->where('experience', $filters['staz']);
        }

        $invitations = ContactInvitation::where('company_id', $request->user()->id)->get()->keyBy('driver_id');

        return view('company.drivers', [
            'drivers' => $query->with('user')->latest()->paginate(20)->withQueryString(),
            'filters' => $filters,
            'invitations' => $invitations,
        ]);
    }

    public function invite(Request $request, User $driver): RedirectResponse
    {
        abort_unless($driver->isDriver() && $driver->driverProfile?->in_database && $driver->hasVerifiedPhone(), 404);

        $data = $request->validate(['message' => ['required', 'string', 'max:1000']]);

        ContactInvitation::firstOrCreate(
            ['company_id' => $request->user()->id, 'driver_id' => $driver->id],
            ['message' => $data['message']],
        );

        return back()->with('status', __('Zaproszenie wysłane. Kierowca sam zdecyduje, czy odpowiedzieć.'));
    }
}
