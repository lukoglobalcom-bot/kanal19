<?php

namespace App\Services;

use RuntimeException;

/**
 * Minimalna implementacja Web Push bez zewnętrznych bibliotek:
 * szyfrowanie treści RFC 8291 (aes128gcm) i podpis VAPID RFC 8292 (ES256), wszystko przez OpenSSL.
 */
class WebPush
{
    /** Prefiks DER klucza publicznego P-256 (SubjectPublicKeyInfo) przed 65-bajtowym punktem. */
    private const P256_SPKI_PREFIX = '3059301306072a8648ce3d020106082a8648ce3d030107034200';

    public static function b64(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    public static function unb64(string $s): string
    {
        $bin = base64_decode(strtr($s, '-_', '+/').str_repeat('=', (4 - strlen($s) % 4) % 4), true);
        if ($bin === false) {
            throw new RuntimeException('Niepoprawne base64url');
        }

        return $bin;
    }

    /** Nowa para kluczy P-256: ['publicKey' => base64url(65 bajtów), 'privateKey' => PEM]. */
    public static function createKeys(): array
    {
        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        openssl_pkey_export($key, $pem);

        return ['publicKey' => self::b64(self::rawPublic($key)), 'privateKey' => $pem];
    }

    private static function rawPublic($key): string
    {
        $d = openssl_pkey_get_details($key)['ec'];

        return "\x04".str_pad($d['x'], 32, "\0", STR_PAD_LEFT).str_pad($d['y'], 32, "\0", STR_PAD_LEFT);
    }

    private static function publicFromRaw(string $raw)
    {
        if (strlen($raw) !== 65 || $raw[0] !== "\x04") {
            throw new RuntimeException('Niepoprawny klucz p256dh');
        }
        $der = hex2bin(self::P256_SPKI_PREFIX).$raw;
        $pem = "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($der), 64, "\n")."-----END PUBLIC KEY-----\n";

        return openssl_pkey_get_public($pem);
    }

    /** Szyfruje treść dla subskrypcji (p256dh i auth w base64url). Zwraca gotowe ciało żądania. */
    public static function encrypt(string $payload, string $p256dh, string $auth): string
    {
        $uaPublic = self::unb64($p256dh);
        $authSecret = self::unb64($auth);

        $local = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $asPublic = self::rawPublic($local);
        $shared = openssl_pkey_derive(self::publicFromRaw($uaPublic), $local, 32);
        if ($shared === false) {
            throw new RuntimeException('ECDH nie powiódł się');
        }

        $ikm = hash_hkdf('sha256', $shared, 32, "WebPush: info\0".$uaPublic.$asPublic, $authSecret);
        $salt = random_bytes(16);
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt);

        $cipher = openssl_encrypt($payload."\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);

        return $salt.pack('N', 4096).chr(65).$asPublic.$cipher.$tag;
    }

    /** Nagłówek Authorization VAPID dla danego adresu push. */
    public static function vapidHeader(string $endpoint, string $publicKey, string $privatePem, string $subject): string
    {
        $parts = parse_url($endpoint);
        $aud = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
        $head = self::b64(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $body = self::b64(json_encode(['aud' => $aud, 'exp' => time() + 12 * 3600, 'sub' => $subject], JSON_UNESCAPED_SLASHES));
        openssl_sign($head.'.'.$body, $der, openssl_pkey_get_private($privatePem), OPENSSL_ALGO_SHA256);

        return 'vapid t='.$head.'.'.$body.'.'.self::b64(self::derToRaw($der)).', k='.$publicKey;
    }

    /** Podpis ECDSA z DER (SEQUENCE r, s) do formatu JWS (r||s, po 32 bajty). */
    private static function derToRaw(string $der): string
    {
        $pos = 2;
        if (ord($der[1]) & 0x80) {
            $pos += ord($der[1]) & 0x7F;
        }
        $out = '';
        for ($i = 0; $i < 2; $i++) {
            $len = ord($der[$pos + 1]);
            $int = substr($der, $pos + 2, $len);
            $out .= str_pad(ltrim($int, "\0"), 32, "\0", STR_PAD_LEFT);
            $pos += 2 + $len;
        }

        return $out;
    }
}
