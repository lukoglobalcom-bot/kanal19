<?php

namespace App\Support;

/**
 * Grafiki portalu z public/images/grafiki (banery działów N01–N21, zdjęcia A01–A16, logo i ikony).
 * Banery z napisami mają wersje językowe w podfolderach (np. grafiki/de/N02-praca.webp).
 * Pliki leżą tylko na serwerze – gdy pliku nie ma, helper zwraca null, a widok go pomija.
 */
class Graphics
{
    private static function file(string $rel): ?string
    {
        $rel = 'images/grafiki/'.$rel;

        return is_file(public_path($rel)) ? asset($rel) : '';
    }

    /** Baner w języku użytkownika; po polsku – wersja główna; bez tłumaczenia – zdjęcie zastępcze (bez napisów). */
    public static function banner(string $name, ?string $fallbackPhoto = null): ?string
    {
        $locale = app()->getLocale();
        $url = $locale === 'pl' ? self::file($name.'.webp') : self::file($locale.'/'.$name.'.webp');
        if (! $url && $fallbackPhoto) {
            $url = self::file($fallbackPhoto.'.webp');
        }

        return $url ?: null;
    }

    public static function asset(string $file): ?string
    {
        return self::file($file) ?: null;
    }
}
