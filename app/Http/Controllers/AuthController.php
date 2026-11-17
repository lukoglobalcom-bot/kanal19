<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SmsSender;
use App\Support\Languages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Logowanie, rejestracja trzech typów kont i weryfikacja telefonu kodem SMS.
 */
class AuthController extends Controller
{
    public const EXPERIENCE = [
        'none' => 'Bez doświadczenia', 'lt1' => 'Do 1 roku', '1-3' => '1–3 lata',
        '3-5' => '3–5 lat', '5-10' => '5–10 lat', 'gt10' => 'Ponad 10 lat',
    ];

    public const VEHICLES = [
        'firanka' => 'Ciągnik + firanka', 'chlodnia' => 'Chłodnia', 'cysterna' => 'Cysterna',
        'wywrotka' => 'Wywrotka', 'kontener' => 'Kontenery', 'hds' => 'HDS', 'solowka' => 'Solówka',
        'bus' => 'Bus do 3,5 t', 'autobus' => 'Autobus / autokar', 'laweta' => 'Laweta',
        'tandem' => 'Tandem / jumbo', 'ponad' => 'Transport ponadnormatywny',
    ];

    public const LICENCES = ['B', 'B+E', 'C', 'C+E', 'D', 'Kod 95', 'ADR', 'HDS'];

    public const BUSINESS_TYPES = [
        'carrier' => 'Firma transportowa / przewoźnik', 'logistics' => 'Spedycja i logistyka',
        'agency' => 'Agencja pracy', 'passenger' => 'Komunikacja / przewóz osób', 'other' => 'Inna działalność',
    ];

    public const AD_FORMATS = [
        'billboard' => 'Billboard na stronie głównej', 'box' => 'Box przy ofertach',
        'partner' => 'Partner sekcji / artykuł', 'radio' => 'Spot w Kanał 19 Radio', 'unknown' => 'Jeszcze nie wiem',
    ];

    public function showLogin(Request $request): View
    {
        return view('auth.login', ['role' => $this->roleFrom($request)]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:'.Str::lower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => __('Za dużo prób logowania. Spróbuj za :sec s.', ['sec' => RateLimiter::availableIn($key)])])->onlyInput('email');
        }

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['email' => __('Nieprawidłowy e-mail lub hasło.')])->onlyInput('email');
        }

        RateLimiter::clear($key);

        if ($request->user()->isBlocked()) {
            Auth::logout();

            return back()->withErrors(['email' => __('To konto zostało zablokowane.')])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($request->user()));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showRegister(Request $request): View
    {
        return view('auth.register', ['role' => $this->roleFrom($request)]);
    }

    public function register(Request $request): RedirectResponse
    {
        $role = $request->input('role');
        abort_unless(in_array($role, User::ROLES, true), 422);

        $common = [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9 ]{9,20}$/'],
            'locale' => ['required', Rule::in(array_keys(Languages::ALL))],
            'terms' => ['accepted'],
        ];

        $specific = match ($role) {
            'driver' => [
                'first_name' => ['required', 'string', 'max:60'],
                'last_name' => ['required', 'string', 'max:60'],
                'experience' => ['required', Rule::in(array_keys(self::EXPERIENCE))],
                'vehicles' => ['required', 'array', 'min:1'],
                'vehicles.*' => [Rule::in(array_keys(self::VEHICLES))],
                'licences' => ['required', 'array', 'min:1'],
                'licences.*' => [Rule::in(self::LICENCES)],
            ],
            'company' => [
                'company_name' => ['required', 'string', 'max:120'],
                'nip' => ['required', 'digits:10', function ($attr, $value, $fail) {
                    if (! self::validNip($value)) {
                        $fail(__('Nieprawidłowy NIP.'));
                    }
                }],
                'business_type' => ['required', Rule::in(array_keys(self::BUSINESS_TYPES))],
            ],
            'advertiser' => [
                'brand' => ['required', 'string', 'max:120'],
                'nip' => ['required', 'digits:10', function ($attr, $value, $fail) {
                    if (! self::validNip($value)) {
                        $fail(__('Nieprawidłowy NIP.'));
                    }
                }],
                'website' => ['nullable', 'url', 'max:255'],
                'ad_format' => ['nullable', Rule::in(array_keys(self::AD_FORMATS))],
            ],
        };

        $data = $request->validate($common + $specific);

        $user = DB::transaction(function () use ($data, $role, $request) {
            $name = match ($role) {
                'driver' => $data['first_name'].' '.$data['last_name'],
                'company' => $data['company_name'],
                'advertiser' => $data['brand'],
            };

            $user = new User([
                'name' => $name,
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => preg_replace('/\s+/', '', $data['phone']),
                'locale' => $data['locale'],
            ]);
            $user->role = $role;
            $user->save();

            match ($role) {
                'driver' => $user->driverProfile()->create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'experience' => $data['experience'],
                    'vehicles' => array_values($data['vehicles']),
                    'licences' => array_values($data['licences']),
                    'in_database' => $request->boolean('in_database'),
                ]),
                'company' => $user->companyProfile()->create([
                    'name' => $data['company_name'],
                    'nip' => $data['nip'],
                    'business_type' => $data['business_type'],
                ]),
                'advertiser' => $user->advertiserProfile()->create([
                    'brand' => $data['brand'],
                    'nip' => $data['nip'],
                    'website' => $data['website'] ?? null,
                    'ad_format' => $data['ad_format'] ?? null,
                ]),
            };

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        if (! SmsSender::required()) {
            return redirect($this->homeFor($user))->with('status', __('Konto założone. Witamy w Kanale 19!'));
        }

        app(SmsSender::class)->sendCode($user);

        return redirect()->route('verify.show')->with('status', __('Wysłaliśmy kod SMS na numer :phone.', ['phone' => $user->phone]));
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedPhone()) {
            return redirect($this->homeFor($request->user()));
        }

        return view('auth.verify');
    }

    public function verify(Request $request, SmsSender $sms): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        if (! $sms->verify($request->user(), $request->input('code'))) {
            return back()->withErrors(['code' => __('Kod jest nieprawidłowy lub wygasł. Wyślij nowy kod.')]);
        }

        return redirect($this->homeFor($request->user()))->with('status', __('Numer potwierdzony. Konto jest aktywne.'));
    }

    public function resend(Request $request, SmsSender $sms): RedirectResponse
    {
        if (! $sms->sendCode($request->user())) {
            return back()->withErrors(['code' => __('Nowy kod możesz wysłać za 60 sekund.')]);
        }

        return back()->with('status', __('Wysłaliśmy nowy kod SMS.'));
    }

    public function setLanguage(Request $request): RedirectResponse
    {
        $data = $request->validate(['locale' => ['required', Rule::in(array_keys(Languages::ALL))]]);
        $request->session()->put('locale', $data['locale']);
        if ($request->user()) {
            $request->user()->forceFill(['locale' => $data['locale']])->save();
        }

        return back();
    }

    public static function validNip(string $nip): bool
    {
        if (! preg_match('/^\d{10}$/', $nip)) {
            return false;
        }
        $w = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $w[$i] * (int) $nip[$i];
        }

        return $sum % 11 === (int) $nip[9];
    }

    private function roleFrom(Request $request): string
    {
        $role = $request->query('konto', 'driver');

        return in_array($role, User::ROLES, true) ? $role : 'driver';
    }

    private function homeFor(User $user): string
    {
        return match ($user->role) {
            'company' => route('company.offers'),
            'admin' => route('admin.index'),
            'advertiser' => route('advertiser.index'),
            default => route('driver.profile'),
        };
    }
}
