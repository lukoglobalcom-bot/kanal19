<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Oferta pracy. Po publikacji jest widoczna przez 30 dni i firma nie może jej edytować.
 */
class JobOffer extends Model
{
    use HasFactory;

    public const VISIBLE_DAYS = 30;

    public const CATEGORIES = [
        'b' => 'Kat. B',
        'c' => 'Kat. C',
        'ce' => 'Kat. C+E',
        'd' => 'Kat. D',
        'taxi' => 'Taxi',
        'kurier' => 'Kurier',
        'adr' => 'ADR',
        'bud' => 'Budowa / HDS',
        'abroad' => 'Za granicą',
        'newbie' => 'Bez doświadczenia',
    ];

    public const CONTRACTS = [
        'uop' => 'Umowa o pracę',
        'b2b' => 'Kontrakt B2B',
        'zlecenie' => 'Umowa zlecenie',
    ];

    public const WORK_MODES = [
        'home_daily' => 'Codziennie w domu',
        'weekends_off' => 'Weekendy wolne',
        'system' => 'System 2/1, 3/1',
    ];

    public const CURRENCIES = ['PLN', 'EUR', 'CZK', 'HUF', 'RON', 'SEK', 'DKK', 'BGN'];

    protected $fillable = [
        'title', 'category', 'city', 'country', 'salary_from', 'salary_to', 'currency',
        'salary_type', 'contract', 'work_modes', 'description', 'requirements', 'no_cv', 'language',
    ];

    protected function casts(): array
    {
        return [
            'work_modes' => 'array',
            'no_cv' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /** Tylko opublikowane i jeszcze niewygasłe oferty. */
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

    /** Publikacja: od teraz oferta jest widoczna 30 dni i zablokowana do edycji. */
    public function publish(): void
    {
        $this->published_at = now();
        $this->expires_at = now()->addDays(self::VISIBLE_DAYS);
        $this->save();
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function contractLabel(): string
    {
        return self::CONTRACTS[$this->contract] ?? $this->contract;
    }

    public function salaryLabel(): string
    {
        $symbol = ['PLN' => 'zł', 'EUR' => '€', 'CZK' => 'Kč', 'HUF' => 'Ft', 'RON' => 'lei', 'SEK' => 'kr', 'DKK' => 'kr', 'BGN' => 'лв'][$this->currency] ?? $this->currency;
        $fmt = fn ($n) => number_format($n, 0, ',', ' ');

        if ($this->salary_from && $this->salary_to) {
            return $fmt($this->salary_from).'–'.$fmt($this->salary_to).' '.$symbol;
        }
        if ($this->salary_from) {
            return __('od').' '.$fmt($this->salary_from).' '.$symbol;
        }

        return __('Do uzgodnienia');
    }
}
