<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zgłoszenie na mapie kierowców: parking (wolne miejsca), paliwo (cena) albo SOS (wezwanie pomocy).
 */
class MapReport extends Model
{
    /** Ile godzin zgłoszenie jest widoczne. */
    public const TTL_HOURS = ['parking' => 6, 'fuel' => 168, 'sos' => 6];

    public const SOS_PROBLEMS = [
        'vehicle' => 'Problem z pojazdem',
        'trailer' => 'Problem z naczepą',
        'health' => 'Problem ze zdrowiem',
        'life' => 'Zagrożenie życia',
    ];

    /** Waluta paliwa zależy od kraju stacji. Pozostałe kraje strefy euro – EUR. */
    public const CURRENCY_BY_COUNTRY = [
        'PL' => 'PLN', 'CZ' => 'CZK', 'HU' => 'HUF', 'RO' => 'RON', 'SE' => 'SEK', 'DK' => 'DKK',
        'CH' => 'CHF', 'GB' => 'GBP', 'NO' => 'NOK', 'UA' => 'UAH', 'RS' => 'RSD', 'TR' => 'TRY',
    ];

    public const COUNTRIES = [
        'PL', 'DE', 'CZ', 'SK', 'LT', 'LV', 'EE', 'HU', 'AT', 'NL', 'BE', 'LU', 'FR', 'IT', 'ES', 'PT', 'DK', 'SE', 'FI',
        'IE', 'SI', 'HR', 'RO', 'BG', 'GR', 'CY', 'MT', 'CH', 'NO', 'GB', 'UA', 'RS', 'TR',
    ];

    protected $fillable = ['kind', 'lat', 'lng', 'name', 'country', 'data'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'lat' => 'float',
            'lng' => 'float',
            'expires_at' => 'datetime',
        ];
    }

    public static function currencyFor(string $country): string
    {
        return self::CURRENCY_BY_COUNTRY[strtoupper($country)] ?? 'EUR';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SosResponse::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }
}
