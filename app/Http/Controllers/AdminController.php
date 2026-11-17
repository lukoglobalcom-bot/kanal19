<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobOfferRequest;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Panel administratora: użytkownicy (blokowanie) i oferty (edycja, usuwanie). */
class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'stats' => [
                'Kierowcy' => User::where('role', 'driver')->count(),
                'Firmy' => User::where('role', 'company')->count(),
                'Reklamodawcy' => User::where('role', 'advertiser')->count(),
                'Aktywne oferty' => JobOffer::active()->count(),
            ],
            'users' => User::where('role', '!=', 'admin')->latest()->limit(50)->get(),
            'offers' => JobOffer::with('company')->withCount('applications')->latest()->limit(50)->get(),
            'vehicles' => \App\Models\VehicleListing::with('seller')->latest()->limit(50)->get(),
            'campaigns' => \App\Models\AdCampaign::with('advertiser')->withCount('creatives')->latest()->limit(50)->get(),
        ]);
    }

    public function toggleCampaign(\App\Models\AdCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->isAccepted(), 422);
        $campaign->forceFill(['active' => ! $campaign->active])->save();

        return back()->with('status', $campaign->active ? __('Kampania włączona.') : __('Kampania wyłączona.'));
    }

    public function toggleBlock(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);
        $user->forceFill(['blocked_at' => $user->isBlocked() ? null : now()])->save();

        return back()->with('status', $user->isBlocked() ? __('Konto zablokowane.') : __('Konto odblokowane.'));
    }

    public function editOffer(JobOffer $offer): View
    {
        $this->authorize('update', $offer);

        return view('company.offer-form', ['offer' => $offer, 'admin' => true]);
    }

    public function updateOffer(JobOfferRequest $request, JobOffer $offer): RedirectResponse
    {
        $this->authorize('update', $offer);
        $offer->update($request->validated());

        return redirect()->route('admin.index')->with('status', __('Oferta zapisana.'));
    }

    public function destroyOffer(JobOffer $offer): RedirectResponse
    {
        $this->authorize('delete', $offer);
        $offer->delete();

        return back()->with('status', __('Oferta usunięta.'));
    }
}
