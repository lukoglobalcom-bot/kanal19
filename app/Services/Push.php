<?php

namespace App\Services;

use App\Models\MapReport;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

/**
 * Powiadomienia push (Web Push, klucze VAPID tworzone automatycznie w storage/app/vapid.json, szyfrowanie w App\Services\WebPush).
 */
class Push
{
    public static function keys(): array
    {
        $disk = Storage::disk('local');
        if (! $disk->exists('vapid.json')) {
            $disk->put('vapid.json', json_encode(WebPush::createKeys()));
        }

        return json_decode($disk->get('vapid.json'), true);
    }

    public static function publicKey(): string
    {
        return self::keys()['publicKey'];
    }

    /** Wysyła wezwanie SOS do kierowców, których ostatnia pozycja jest w ich promieniu powiadomień. */
    public static function sos(MapReport $report): int
    {
        $subs = self::targets($report);
        if ($subs->isEmpty() || ! config('services.push.enabled', true)) {
            return 0;
        }

        $current = App::getLocale();
        $messages = [];
        foreach ($subs as [$sub, $km]) {
            App::setLocale($sub->locale);
            $problem = __(MapReport::SOS_PROBLEMS[$report->data['problem'] ?? ''] ?? 'SOS');
            $messages[] = [$sub, json_encode([
                'title' => 'SOS – '.$problem,
                'body' => trim(__('Kierowca potrzebuje pomocy').' · '.round($km).' km'.(! empty($report->data['message']) ? ' · '.$report->data['message'] : '')),
                'url' => url('/mapa').'?sos='.$report->id.'#k19-map',
                'tag' => 'sos-'.$report->id,
            ], JSON_UNESCAPED_UNICODE)];
        }
        App::setLocale($current);

        return self::deliver($messages);
    }

    /** Subskrypcje w zasięgu wezwania: [subskrypcja, odległość km]. */
    public static function targets(MapReport $report)
    {
        $maxKm = 150;
        $dLat = $maxKm / 111;
        $dLng = $maxKm / (111 * max(0.2, cos(deg2rad($report->lat))));

        return PushSubscription::query()
            ->where('user_id', '!=', $report->user_id)
            ->where('located_at', '>', now()->subHours(PushSubscription::LOCATION_HOURS))
            ->whereBetween('lat', [$report->lat - $dLat, $report->lat + $dLat])
            ->whereBetween('lng', [$report->lng - $dLng, $report->lng + $dLng])
            ->whereHas('user', fn ($q) => $q->whereNull('blocked_at'))
            ->get()
            ->map(fn (PushSubscription $s) => [$s, PushSubscription::distanceKm($report->lat, $report->lng, $s->lat, $s->lng)])
            ->filter(fn ($pair) => $pair[1] <= $pair[0]->radius_km)
            ->take(500)->values();
    }

    /** @param  array<int, array{0: PushSubscription, 1: string}>  $messages */
    public static function deliver(array $messages): int
    {
        $keys = self::keys();
        $sent = 0;
        foreach (array_chunk($messages, 50) as $chunk) {
            try {
                $responses = Http::pool(function (Pool $pool) use ($chunk, $keys) {
                    foreach ($chunk as $i => [$sub, $payload]) {
                        $pool->as((string) $i)->timeout(10)->withHeaders([
                            'Authorization' => WebPush::vapidHeader($sub->endpoint, $keys['publicKey'], $keys['privateKey'], 'mailto:admin@kanal19.pl'),
                            'Content-Encoding' => 'aes128gcm',
                            'TTL' => '3600',
                            'Urgency' => 'high',
                        ])->withBody(WebPush::encrypt($payload, $sub->p256dh, $sub->auth), 'application/octet-stream')
                            ->post($sub->endpoint);
                    }
                });
            } catch (\Throwable $e) {
                Log::warning('Push SOS nie wysłany: '.$e->getMessage());

                continue;
            }
            foreach ($chunk as $i => [$sub]) {
                $res = $responses[(string) $i] ?? null;
                if ($res instanceof \Illuminate\Http\Client\Response) {
                    if ($res->successful()) {
                        $sent++;
                    } elseif (in_array($res->status(), [404, 410], true)) {
                        $sub->delete(); // subskrypcja wygasła
                    }
                }
            }
        }

        return $sent;
    }
}
