<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Konto na kanal19.pl. Rola decyduje, co użytkownik może robić:
 * driver (kierowca), company (firma dodająca ogłoszenia), advertiser (reklamodawca), admin.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['driver', 'company', 'advertiser'];

    /**
     * Pola, które można zapisać z formularza. Rola i daty weryfikacji/blokady
     * NIE są tu wymienione celowo – ustawia je tylko kod portalu.
     */
    protected $fillable = ['name', 'email', 'password', 'locale', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    /** Te same wartości domyślne co w bazie, żeby nowy obiekt od razu je znał. */
    protected $attributes = ['role' => 'driver', 'locale' => 'pl'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'blocked_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function isCompany(): bool
    {
        return $this->role === 'company';
    }

    public function isAdvertiser(): bool
    {
        return $this->role === 'advertiser';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null || ! \App\Services\SmsSender::required();
    }

    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function advertiserProfile(): HasOne
    {
        return $this->hasOne(AdvertiserProfile::class);
    }

    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /** Nazwa wyświetlana: firma albo imię kierowcy. */
    public function displayName(): string
    {
        return match ($this->role) {
            'company' => $this->companyProfile?->name ?? $this->name,
            'advertiser' => $this->advertiserProfile?->brand ?? $this->name,
            default => $this->name,
        };
    }
}
