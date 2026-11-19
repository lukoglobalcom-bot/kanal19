<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\Push;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Zapis na powiadomienia SOS w pobliżu i aktualizacja pozycji kierowcy. */
class PushController extends Controller
{
    public function key(): JsonResponse
    {
        return response()->json(['key' => Push::publicKey()]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $d = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:1000'],
            'keys.p256dh' => ['required', 'string', 'max:200'],
            'keys.auth' => ['required', 'string', 'max:100'],
            'radius_km' => ['nullable', 'integer', 'in:10,25,50,100,150'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $hash = PushSubscription::hashEndpoint($d['endpoint']);
        $sub = PushSubscription::firstOrNew(['endpoint_hash' => $hash]);
        $sub->endpoint_hash = $hash;
        $sub->user_id = $request->user()->id;
        $sub->fill([
            'endpoint' => $d['endpoint'],
            'p256dh' => $d['keys']['p256dh'],
            'auth' => $d['keys']['auth'],
            'locale' => app()->getLocale(),
            'radius_km' => $d['radius_km'] ?? 50,
        ]);
        if (isset($d['lat'], $d['lng'])) {
            $sub->fill(['lat' => $d['lat'], 'lng' => $d['lng'], 'located_at' => now()]);
        }
        $sub->save();

        return response()->json(['ok' => true, 'message' => __('Powiadomienia SOS włączone.')]);
    }

    public function locate(Request $request): JsonResponse
    {
        $d = $request->validate([
            'endpoint' => ['required', 'string', 'max:1000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);
        $n = PushSubscription::where('endpoint_hash', PushSubscription::hashEndpoint($d['endpoint']))
            ->where('user_id', $request->user()->id)
            ->update(['lat' => $d['lat'], 'lng' => $d['lng'], 'located_at' => now(), 'locale' => app()->getLocale()]);

        return response()->json(['ok' => $n > 0]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $endpoint = $request->validate(['endpoint' => ['required', 'string', 'max:1000']])['endpoint'];
        PushSubscription::where('endpoint_hash', PushSubscription::hashEndpoint($endpoint))
            ->where('user_id', $request->user()->id)->delete();

        return response()->json(['ok' => true, 'message' => __('Powiadomienia SOS wyłączone.')]);
    }
}
