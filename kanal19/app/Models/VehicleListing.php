<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ogłoszenie na giełdzie pojazdów. Po publikacji widoczne 30 dni, właściciel może je poprawiać i usunąć.
 */
class VehicleListing extends Model
{
    public const VISIBLE_DAYS = 30;

    public const MAX_PHOTOS = 8;

    public const TYPES = [
        'ciagnik' => 'Ciągnik siodłowy',
        'naczepa' => 'Naczepa / przyczepa',
        'ciezarowy' => 'Samochód ciężarowy',
        'dostawczy' => 'Dostawczy do 3,5 t',
        'autobus' => 'Autobus / autokar',
        'osobowy' => 'Osobowy',
        'maszyna' => 'Maszyna budowlana',
    ];

    public const CONDITIONS = [
        'new' => 'Nowy',
        'used' => 'Używany',
        'leasing' => 'Poleasingowy',
    ];

    public const FUELS = ['diesel' => 'Diesel', 'lng' => 'LNG / CNG', 'electric' => 'Elektryczny', 'petrol' => 'Benzyna', 'hybrid' => 'Hybryda'];

    public const GEARBOXES = ['auto' => 'Automatyczna', 'manual' => 'Manualna'];

    public const EURO = ['euro6', 'euro5', 'euro4', 'euro3'];

    public const CURRENCIES = ['PLN', 'EUR'];

    protected $fillable = [
        'type', 'condition', 'make', 'model', 'year', 'mileage', 'power_hp', 'fuel', 'gearbox', 'euro',
        'price', 'currency', 'price_type', 'vat_invoice', 'city', 'country', 'description', 'phone',
    ];

    protected function casts(): array
    {
        return [
            'vat_invoice' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->orderBy('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('expires_at', '>', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function isActive(): bool
    {
        return $this->isPublished() && $this->expires_at?->isFuture();
    }

    public function publish(): void
    {
        $this->published_at = now();
        $this->expires_at = now()->addDays(self::VISIBLE_DAYS);
        $this->save();
    }

    public function title(): string
    {
        return trim($this->make.' '.$this->model.($this->year ? ' '.$this->year : ''));
    }

    public function priceLabel(): string
    {
        return number_format($this->price, 0, ',', ' ').' '.($this->currency === 'EUR' ? '€' : 'zł');
    }

    public function cover(): ?VehiclePhoto
    {
        return $this->photos->first();
    }
}
