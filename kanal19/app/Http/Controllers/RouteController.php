<?php

namespace App\Http\Controllers;

use App\Models\MapReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Planer trasy dla ciężarówek i autobusów.
 * Mapy: OpenStreetMap. Trasy liczy własny serwer Valhalla (ROUTING_URL), wyszukiwanie adresów – Nominatim.
 * Zgłoszone przez kierowców zamknięcia dróg, niskie wiadukty i limity masy są omijane.
 */
class RouteController extends Controller
{
    /** Gotowe zestawy parametrów pojazdu. */
    public const PRESETS = [
        'tir' => ['label' => 'Ciągnik z naczepą 40 t', 'height' => 4.0, 'width' => 2.55, 'length' => 16.5, 'weight' => 40, 'axle_load' => 11.5, 'axle_count' => 5],
        'solo' => ['label' => 'Ciężarówka solo 18 t', 'height' => 3.8, 'width' => 2.55, 'length' => 10, 'weight' => 18, 'axle_load' => 11.5, 'axle_count' => 2],
        'bus' => ['label' => 'Bus do 3,5 t', 'height' => 2.8, 'width' => 2.1, 'length' => 6.5, 'weight' => 3.5, 'axle_load' => 2, 'axle_count' => 2],
        'coach' => ['label' => 'Autokar', 'height' => 3.9, 'width' => 2.55, 'length' => 13.5, 'weight' => 19, 'axle_load' => 11.5, 'axle_count' => 3],
    ];

    /** Języki wskazówek obsługiwane przez Valhallę. Pozostałe – angielski. */
    private const LANGUAGES = [
        'pl' => 'pl-PL', 'en' => 'en-GB', 'de' => 'de-DE', 'uk' => 'uk-UA', 'cs' => 'cs-CZ', 'sk' => 'sk-SK',
        'bg' => 'bg-BG', 'da' => 'da-DK', 'el' => 'el-GR', 'es' => 'es-ES', 'et' => 'et-EE', 'fi' => 'fi-FI',
        'fr' => 'fr-FR', 'hu' => 'hu-HU', 'it' => 'it-IT', 'nl' => 'nl-NL', 'pt' => 'pt-PT', 'ro' => 'ro-RO',
        'sl' => 'sl-SI', 'sv' => 'sv-SE',
    ];

    public static function enabled(): bool
    {
        return (bool) config('services.routing.url');
    }

    public function index(): View
    {
        return view('route.index', ['presets' => self::PRESETS, 'enabled' => self::enabled()]);
    }

    /** Wyszukiwanie miejsc (tylko po kliknięciu „Szukaj”, wyniki trzymane w pamięci podręcznej 7 dni). */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->validate(['q' => ['required', 'string', 'min:3', 'max:120']])['q']);
        $lang = app()->getLocale();

        $results = Cache::remember('geo:'.$lang.':'.mb_strtolower($q), now()->addDays(7), function () use ($q, $lang) {
            // Nominatim pozwala na 1 zapytanie na sekundę – kolejkujemy zapytania całego portalu
            return Cache::lock('nominatim', 3)->block(5, function () use ($q, $lang) {
                $started = microtime(true);
                $res = Http::withHeaders(['User-Agent' => 'Kanal19.pl route planner (https://kanal19.pl)', 'Accept-Language' => $lang])
                    ->timeout(8)->get(rtrim(config('services.geocoding.url'), '/').'/search', [
                        'q' => $q, 'format' => 'jsonv2', 'limit' => 5, 'addressdetails' => 0,
                    ]);
                usleep(max(0, (int) (1_050_000 - (microtime(true) - $started) * 1_000_000)));
                if (! $res->ok()) {
                    return null;
                }

                return collect($res->json())->map(fn ($r) => [
                    'name' => $r['display_name'] ?? '',
                    'lat' => round((float) $r['lat'], 6),
                    'lng' => round((float) $r['lon'], 6),
                ])->values()->all();
            });
        });

        if ($results === null) {
            Cache::forget('geo:'.$lang.':'.mb_strtolower($q));

            return response()->json(['message' => __('Wyszukiwarka adresów nie odpowiada. Spróbuj za chwilę albo kliknij na mapie.')], 503);
        }

        return response()->json($results);
    }

    public function plan(Request $request): JsonResponse
    {
        $d = $request->validate([
            'points' => ['required', 'array', 'min:2', 'max:5'],
            'points.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'points.*.lng' => ['required', 'numeric', 'between:-180,180'],
            'height' => ['required', 'numeric', 'min:1.5', 'max:5'],
            'width' => ['required', 'numeric', 'min:1.5', 'max:3.5'],
            'length' => ['required', 'numeric', 'min:3', 'max:30'],
            'weight' => ['required', 'numeric', 'min:1', 'max:60'],
            'axle_load' => ['required', 'numeric', 'min:1', 'max:20'],
            'axle_count' => ['required', 'integer', 'min:2', 'max:10'],
            'hazmat' => ['nullable', 'boolean'],
            'avoid_tolls' => ['nullable', 'boolean'],
            'avoid_ferries' => ['nullable', 'boolean'],
        ]);

        if (! self::enabled()) {
            return response()->json(['message' => __('Planer tras startuje wkrótce – uruchamiamy własny serwer map.')], 503);
        }

        $avoid = $this->avoidedReports($d['points'], (float) $d['height'], (float) $d['weight']);

        $body = [
            'locations' => collect($d['points'])->map(fn ($p) => ['lat' => (float) $p['lat'], 'lon' => (float) $p['lng']])->all(),
            'costing' => 'truck',
            'costing_options' => ['truck' => [
                'height' => (float) $d['height'], 'width' => (float) $d['width'], 'length' => (float) $d['length'],
                'weight' => (float) $d['weight'], 'axle_load' => (float) $d['axle_load'], 'axle_count' => (int) $d['axle_count'],
                'hazmat' => (bool) ($d['hazmat'] ?? false),
                'use_tolls' => ($d['avoid_tolls'] ?? false) ? 0.0 : 0.5,
                'use_ferry' => ($d['avoid_ferries'] ?? false) ? 0.0 : 0.5,
            ]],
            'directions_options' => ['units' => 'kilometers', 'language' => self::LANGUAGES[app()->getLocale()] ?? 'en-GB'],
            'alternates' => 1,
        ];
        if ($avoid->isNotEmpty()) {
            $body['exclude_locations'] = $avoid->map(fn (MapReport $r) => ['lat' => $r->lat, 'lon' => $r->lng])->values()->all();
        }

        try {
            $res = Http::withHeaders(['X-K19-Key' => (string) config('services.routing.key')])
                ->timeout(25)->post(rtrim(config('services.routing.url'), '/').'/route', $body);
        } catch (\Throwable) {
            return response()->json(['message' => __('Serwer tras nie odpowiada. Spróbuj za chwilę.')], 503);
        }

        if (! $res->ok() || ! $res->json('trip')) {
            return response()->json(['message' => __('Nie znaleziono trasy dla tego pojazdu. Sprawdź punkty albo parametry.')], 422);
        }

        $trips = array_merge([$res->json('trip')], collect($res->json('alternates') ?? [])->pluck('trip')->filter()->all());

        return response()->json([
            'routes' => collect($trips)->map(fn ($t) => [
                'km' => round((float) ($t['summary']['length'] ?? 0), 1),
                'minutes' => (int) round(($t['summary']['time'] ?? 0) / 60),
                'toll' => (bool) ($t['summary']['has_toll'] ?? false),
                'ferry' => (bool) ($t['summary']['has_ferry'] ?? false),
                'shapes' => collect($t['legs'] ?? [])->pluck('shape')->all(),
                'steps' => collect($t['legs'] ?? [])->flatMap(fn ($l) => $l['maneuvers'] ?? [])->map(fn ($m) => [
                    'text' => $m['instruction'] ?? '',
                    'km' => round((float) ($m['length'] ?? 0), 1),
                ])->values()->all(),
            ])->values(),
            'avoided' => $avoid->count(),
        ]);
    }

    /** Aktywne utrudnienia w prostokącie wokół punktów trasy, których ten pojazd nie może przejechać. */
    private function avoidedReports(array $points, float $height, float $weight)
    {
        $lats = array_column($points, 'lat');
        $lngs = array_column($points, 'lng');
        $pad = 1.0; // ok. 100 km zapasu na objazdy

        return MapReport::active()->where('kind', 'road')
            ->whereBetween('lat', [min($lats) - $pad, max($lats) + $pad])
            ->whereBetween('lng', [min($lngs) - $pad, max($lngs) + $pad])
            ->latest()->limit(200)->get()
            ->filter(fn (MapReport $r) => $r->blocks($height, $weight))
            ->take(50);
    }
}
