<?php

namespace App\Services;

use App\Models\SmsCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Wysyłka i sprawdzanie kodów SMS.
 *
 * Teraz kod trafia do logów (storage/logs/laravel.log), żeby można było testować za darmo.
 * Żeby wysyłać prawdziwe SMS-y, wystarczy podmienić metodę deliver() na wywołanie API
 * wybranej bramki SMS (klucz w pliku .env jako SMS_API_TOKEN).
 */
class SmsSender
{
    public const VALID_MINUTES = 10;

    public const MAX_ATTEMPTS = 3;

    public const RESEND_SECONDS = 60;

    /** Zwraca false, jeśli od ostatniego kodu minęło mniej niż 60 sekund. */
    public function sendCode(User $user): bool
    {
        $last = SmsCode::where('user_id', $user->id)->latest('id')->first();
        if ($last && $last->created_at->diffInSeconds(now()) < self::RESEND_SECONDS) {
            return false;
        }

        SmsCode::where('user_id', $user->id)->delete();

        $code = (string) random_int(100000, 999999);
        SmsCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::VALID_MINUTES),
        ]);

        $this->deliver($user->phone, __('Twój kod kanal19.pl: :code. Ważny :min minut.', ['code' => $code, 'min' => self::VALID_MINUTES]));

        return true;
    }

    /** Sprawdza kod. Po 3 błędnych próbach kod przestaje działać. */
    public function verify(User $user, string $code): bool
    {
        $sms = SmsCode::where('user_id', $user->id)->latest('id')->first();
        if (! $sms || $sms->expires_at->isPast() || $sms->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $sms->code_hash)) {
            $sms->increment('attempts');

            return false;
        }

        $sms->delete();
        $user->forceFill(['phone_verified_at' => now()])->save();

        return true;
    }

    protected function deliver(string $phone, string $text): void
    {
        Log::info('[SMS do '.$phone.'] '.$text);
    }
}
