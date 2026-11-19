<?php

namespace App\Http\Controllers;

use App\Models\MapReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Mapa kierowców: parkingi z wolnymi miejscami, ceny paliwa i wezwania SOS.
 */
class MapController extends Controller
{
    public function index(): View
    {
        return view('map.index');
    }

    public function sos(): View
    {
        return view('map.sos');
    }

    /** Aktywne zgłoszenia do mapy. Telefon wzywającego pomocy widzą tylko zweryfikowani kierowcy. */
    public function data(Request $request): JsonResponse
    {
        $viewer = $request->user();
        $trusted = $viewer && ($viewer->isAdmin() || $viewer->hasVerifiedPhone());

        $reports = MapReport::active()->with('author:id,name,phone')
            ->withCount(['responses', 'votes as confirmations' => fn ($q) => $q->where('still', true)])
            ->latest()->limit(500)->get();
        $helping = $viewer ? \App\Models\SosResponse::where('user_id', $viewer->id)->pluck('map_report_id')->all() : [];

        return response()->json($reports->map(function (MapReport $r) use ($viewer, $trusted, $helping) {
            $data = $r->data;
            $out = [
                'id' => $r->id,
                'kind' => $r->kind,
                'lat' => $r->lat,
                'lng' => $r->lng,
                'name' => $r->name,
                'country' => $r->country,
                'minutes' => (int) $r->created_at->diffInMinutes(now()),
                'mine' => $viewer && (int) $r->user_id === (int) $viewer->id,
            ];
            if ($r->kind === 'sos') {
                $out['problem'] = $data['problem'] ?? null;
                $out['problem_label'] = __(MapReport::SOS_PROBLEMS[$data['problem'] ?? ''] ?? '');
                $out['message'] = $data['message'] ?? null;
                $out['helpers'] = $r->responses_count;
                $out['helping'] = in_array($r->id, $helping, true);
                $out['phone'] = $trusted ? $r->author?->phone : null;
                $out['who'] = $trusted ? $r->author?->name : null;
            } elseif ($r->kind === 'road') {
                $out['data'] = $data;
                $out['problem_label'] = __(MapReport::ROAD_PROBLEMS[$data['problem'] ?? '']['label'] ?? '');
                $out['confirmations'] = (int) $r->confirmations;
            } else {
                $out['data'] = $data;
            }

            return $out;
        })->values());
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $base = $request->validate([
            'kind' => ['required', Rule::in(['parking', 'fuel', 'sos', 'road'])],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'name' => ['nullable', 'string', 'max:120'],
            'country' => ['required', Rule::in(MapReport::COUNTRIES)],
        ]);

        $data = match ($base['kind']) {
            'parking' => $request->validate([
                'free_truck' => ['nullable', 'integer', 'min:0', 'max:999'],
                'free_bus' => ['nullable', 'integer', 'min:0', 'max:999'],
                'free_car' => ['nullable', 'integer', 'min:0', 'max:999'],
                'paid' => ['nullable', 'boolean'],
                'note' => ['nullable', 'string', 'max:200'],
            ]) + ['paid' => $request->boolean('paid')],
            'fuel' => $request->validate([
                'diesel' => ['required', 'numeric', 'min:0.1', 'max:10000'],
                'adblue' => ['nullable', 'numeric', 'min:0.01', 'max:10000'],
            ]) + ['currency' => MapReport::currencyFor($base['country'])],
            'sos' => $request->validate([
                'problem' => ['required', Rule::in(array_keys(MapReport::SOS_PROBLEMS))],
                'message' => ['nullable', 'string', 'max:300'],
            ]),
            'road' => $request->validate([
                'problem' => ['required', Rule::in(array_keys(MapReport::ROAD_PROBLEMS))],
                'road' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z0-9 .\-]+$/'],
                'limit' => ['nullable', 'numeric', 'min:1', 'max:60'],
                'note' => ['nullable', 'string', 'max:200'],
            ]),
        };
        if ($base['kind'] === 'road') {
            $data['road'] = isset($data['road']) && $data['road'] !== '' ? strtoupper(trim($data['road'])) : null;
            if (! in_array($data['problem'], ['low_bridge', 'weight_limit'], true)) {
                unset($data['limit']);
            }
        }

        if ($base['kind'] === 'parking' && collect(['free_truck', 'free_bus', 'free_car'])->every(fn ($k) => ($data[$k] ?? null) === null)) {
            return $this->fail($request, 'free_truck', __('Podaj liczbę wolnych miejsc przynajmniej dla jednego rodzaju pojazdu.'));
        }

        $user = $request->user();
        if ($base['kind'] === 'sos') {
            // Jedno aktywne wezwanie na kierowcę – nowe zastępuje poprzednie
            MapReport::active()->where('kind', 'sos')->where('user_id', $user->id)->update(['expires_at' => now()]);
        }

        $report = new MapReport($base + ['data' => $data]);
        $report->user_id = $user->id;
        $report->expires_at = now()->addHours(MapReport::ttlFor($base['kind'], $data));
        $report->save();

        if ($base['kind'] === 'sos') {
            \App\Services\Push::sos($report);
        }

        $msg = match ($base['kind']) {
            'sos' => __('Wezwanie SOS wysłane. Kierowcy w okolicy widzą je na mapie.'),
            default => __('Dziękujemy! Zgłoszenie jest na mapie.'),
        };

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'id' => $report->id, 'message' => $msg])
            : redirect()->route('map.index')->with('status', $msg);
    }

    public function help(Request $request, MapReport $report): RedirectResponse|JsonResponse
    {
        abort_unless($report->kind === 'sos' && $report->expires_at->isFuture(), 404);
        abort_if((int) $report->user_id === (int) $request->user()->id, 403);
        $report->responses()->firstOrCreate(['user_id' => $request->user()->id]);

        $msg = __('Dziękujemy! Kierowca widzi, że jedziesz z pomocą.');

        return $request->wantsJson() ? response()->json(['ok' => true, 'message' => $msg]) : back()->with('status', $msg);
    }

    /** „Nadal jest?” – kierowca mijający utrudnienie potwierdza je albo zgłasza, że już go nie ma. */
    public function vote(Request $request, MapReport $report): JsonResponse
    {
        abort_unless($report->kind === 'road' && $report->expires_at->isFuture(), 404);
        abort_if((int) $report->user_id === (int) $request->user()->id, 403);
        $still = $request->validate(['still' => ['required', 'boolean']])['still'];
        $still = filter_var($still, FILTER_VALIDATE_BOOL);

        $report->votes()->updateOrCreate(['user_id' => $request->user()->id], ['still' => $still]);

        if ($still) {
            // Potwierdzone – liczymy czas widoczności od nowa
            $until = now()->addHours(MapReport::ttlFor('road', $report->data ?? []));
            if ($until->greaterThan($report->expires_at)) {
                $report->forceFill(['expires_at' => $until])->save();
            }
        } else {
            $yes = $report->votes()->where('still', true)->count();
            $no = $report->votes()->where('still', false)->count();
            if ($no - $yes >= MapReport::GONE_VOTES) {
                $report->forceFill(['expires_at' => now()])->save();
            }
        }

        return response()->json(['ok' => true, 'message' => __('Dziękujemy za potwierdzenie!')]);
    }

    public function close(Request $request, MapReport $report): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || (int) $report->user_id === (int) $user->id, 403);
        $report->forceFill(['expires_at' => now()])->save();

        return $request->wantsJson() ? response()->json(['ok' => true]) : back()->with('status', __('Zgłoszenie zamknięte.'));
    }

    private function fail(Request $request, string $field, string $message): RedirectResponse|JsonResponse
    {
        return $request->wantsJson()
            ? response()->json(['message' => $message, 'errors' => [$field => [$message]]], 422)
            : back()->withErrors([$field => $message])->withInput();
    }
}
