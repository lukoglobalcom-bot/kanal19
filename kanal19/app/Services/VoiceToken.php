<?php

namespace App\Services;

/**
 * Bilet dostępu (JWT HS256) do głosowego CB na serwerze LiveKit.
 * Klucz i sekret: LIVEKIT_KEY / LIVEKIT_SECRET, adres: LIVEKIT_URL (wss://cb.kanal19.pl).
 */
class VoiceToken
{
    public static function enabled(): bool
    {
        return (bool) (config('services.livekit.url') && config('services.livekit.key') && config('services.livekit.secret'));
    }

    public static function room(string $channel): string
    {
        return 'cb-'.strtolower($channel);
    }

    public static function make(string $identity, string $name, string $room, int $ttlMinutes = 120): string
    {
        $now = time();
        $payload = [
            'iss' => config('services.livekit.key'),
            'sub' => $identity,
            'name' => $name,
            'nbf' => $now - 10,
            'exp' => $now + $ttlMinutes * 60,
            'video' => [
                'room' => $room,
                'roomJoin' => true,
                'canPublish' => true,
                'canSubscribe' => true,
                'canPublishData' => false,
                'canPublishSources' => ['microphone'],
            ],
        ];
        $b64 = fn (string $s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
        $head = $b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = $b64(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $sig = $b64(hash_hmac('sha256', $head.'.'.$body, (string) config('services.livekit.secret'), true));

        return $head.'.'.$body.'.'.$sig;
    }
}
