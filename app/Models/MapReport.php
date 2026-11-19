<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zgłoszenie na mapie kierowców: parking (wolne miejsca), paliwo (cena), SOS (wezwanie pomocy)
 * albo utrudnienie na drodze (road) – zamknięcia, niskie wiadukty i limity masy omija planer trasy.
 */
class MapReport extends Model
{
    /** Ile godzin zgłoszenie jest widoczne. */
    public const TTL_HOURS = ['parking' => 6, 'fuel' => 168, 'sos' => 6, 'road' => 24];

    /** Rodzaje utrudnień: nazwa, czas widoczności (h), czy planer trasy ma omijać to miejsce. */
    public const ROAD_PROBLEMS = [
        'closure' => ['label' => 'Droga zamknięta', 'ttl' => 48, 'avoid' => true],
        'roadwork' => ['label' => 'Roboty drogowe', 'ttl' => 72, 'avoid' => false],
        'accident' => ['label' => 'Wypadek', 'ttl' => 3, 'avoid' => false],
        'jam' => ['label' => 'Korek', 'ttl' => 2, 'avoid' => false],
        'police' => ['label' => 'Kontrola (ITD / policja)', 'ttl' => 1, 'avoid' => false],
        'low_bridge' => ['label' => 'Niski wiadukt', 'ttl' => 720, 'avoid' => true],
        'weight_limit' => ['label' => 'Ograniczenie masy', 'ttl' => 720, 'avoid' => true],
        'truck_ban' => ['label' => 'Zakaz dla ciężarówek', 'ttl' => 720, 'avoid' => true],
    ];

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

    public static function ttlFor(string $kind, array $data = []): int
    {
        if ($kind === 'road') {
            return self::ROAD_PROBLEMS[$data['problem'] ?? '']['ttl'] ?? self::TTL_HOURS['road'];
        }

        return self::TTL_HOURS[$kind];
    }

    /**
     * Czy pojazd o podanych wymiarach musi ominąć to utrudnienie.
     * Niski wiadukt / limit masy omijamy, gdy pojazd przekracza limit albo limit nie jest znany.
     */
    public function blocks(float $heightM, float $weightT): bool
    {
        if ($this->kind !== 'road') {
            return false;
        }
        $problem = $this->data['problem'] ?? '';
        $limit = isset($this->data['limit']) ? (float) $this->data['limit'] : null;

        return match ($problem) {
            'closure', 'truck_ban' => true,
            'low_bridge' => $limit === null || $heightM >= $limit,
            'weight_limit' => $limit === null || $weightT > $limit,
            default => false,
        };
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

    public function votes(): HasMany
    {
        return $this->hasMany(MapReportVote::class);
    }

    /** Tyle głosów „nie ma” (przewagą nad „nadal jest”) usuwa utrudnienie z mapy. */
    public const GONE_VOTES = 2;

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }
}
