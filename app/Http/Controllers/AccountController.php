<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/** Konto użytkownika: usunięcie konta (RODO) oraz dokumenty prawne i dane operatora. */
class AccountController extends Controller
{
    public function show(): View
    {
        return view('account.show', ['user' => Auth::user()]);
    }

    /** Trwałe usunięcie konta i wszystkich danych (oferty, ogłoszenia, zgłoszenia, wiadomości). */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'confirm' => ['accepted'],
        ]);
        $user = $request->user();
        if (! Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['password' => __('Nieprawidłowe hasło.')]);
        }
        abort_if($user->isAdmin(), 403, __('Konta administratora nie można usunąć w ten sposób.'));

        foreach ($user->vehicleListings()->pluck('id') as $id) {
            File::deleteDirectory(public_path('uploads/pojazdy/'.$id));
        }
        foreach ($user->adCampaigns()->pluck('id') as $id) {
            File::deleteDirectory(public_path('uploads/reklamy/'.$id));
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', __('Twoje konto i dane zostały usunięte.'));
    }

    public function terms(): View
    {
        return $this->legal('terms');
    }

    public function privacy(): View
    {
        return $this->legal('privacy');
    }

    private function legal(string $doc): View
    {
        $lang = app()->getLocale() === 'pl' ? 'pl' : 'en';
        $op = collect(Setting::OPERATOR_FIELDS)->keys()->mapWithKeys(fn ($k) => [$k => Setting::get($k)])->all();

        return view("legal.$doc-$lang", ['op' => $op, 'updated' => '19.11.2026']);
    }

    public function operatorForm(): View
    {
        return view('admin.operator', ['fields' => Setting::OPERATOR_FIELDS]);
    }

    public function operatorSave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'operator_name' => ['required', 'string', 'max:200'],
            'operator_address' => ['required', 'string', 'max:300'],
            'operator_nip' => ['nullable', 'string', 'max:20'],
            'operator_regon' => ['nullable', 'string', 'max:20'],
            'operator_email' => ['required', 'email', 'max:150'],
        ]);
        Setting::put($data);

        return back()->with('status', __('Zapisano dane operatora.'));
    }
}
