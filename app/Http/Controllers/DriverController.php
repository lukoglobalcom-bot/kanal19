<?php

namespace App\Http\Controllers;

use App\Models\ContactInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Profil kierowcy: aplikacje, zaproszenia od firm, zgoda na bazę kierowców. */
class DriverController extends Controller
{
    public function profile(Request $request): View
    {
        $user = $request->user()->load('driverProfile');

        return view('driver.profile', [
            'user' => $user,
            'applications' => $user->applications()->with('offer.company.companyProfile')->latest()->get(),
            'invitations' => ContactInvitation::where('driver_id', $user->id)->with('company.companyProfile')->latest()->get(),
        ]);
    }

    public function toggleDatabase(Request $request): RedirectResponse
    {
        $profile = $request->user()->driverProfile;
        $profile->in_database = $request->boolean('in_database');
        $profile->save();

        return back()->with('status', $profile->in_database
            ? __('Twój profil jest w bazie kierowców.')
            : __('Usunęliśmy Twój profil z bazy kierowców.'));
    }

    /** Kierowca odpowiada na zaproszenie – dopiero wtedy firma widzi jego telefon. */
    public function acceptInvitation(Request $request, ContactInvitation $invitation): RedirectResponse
    {
        abort_unless((int) $invitation->driver_id === (int) $request->user()->id, 403);
        $invitation->forceFill(['accepted_at' => now()])->save();

        return back()->with('status', __('Firma zobaczy teraz Twój numer telefonu.'));
    }
}
