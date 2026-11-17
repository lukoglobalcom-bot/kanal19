<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Wiadomość na CB Kanał 19. Widoczna 3 godziny, ukrywana po 3 zgłoszeniach. */
class CbMessage extends Model
{
    public const VISIBLE_HOURS = 3;

    public const HIDE_AFTER_REPORTS = 3;

    public const CHANNELS = ['EU', 'PL', 'DE', 'CZ', 'SK', 'LT', 'LV', 'EE', 'NL', 'BE', 'FR', 'IT', 'ES', 'AT', 'HU', 'RO', 'BG', 'DK', 'SE', 'UA'];

    protected $fillable = ['channel', 'road', 'body'];

    protected function casts(): array
    {
        return ['hidden' => 'boolean'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CbReport::class);
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('hidden', false)->where('created_at', '>', now()->subHours(self::VISIBLE_HOURS));
    }

    /** Podpis bez pełnego nazwiska: „Marek N.” albo nazwa firmy. */
    public function signature(): string
    {
        $u = $this->author;
        if (! $u) {
            return '?';
        }
        if ($u->isDriver() && $u->driverProfile) {
            return $u->driverProfile->first_name.' '.mb_substr($u->driverProfile->last_name, 0, 1).'.';
        }

        return $u->displayName();
    }
}
