<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kampania reklamowa w wielu językach. Każdy wybrany język = 1 reklama + 1 artykuł.
 */
class AdCampaign extends Model
{
    public const FORMATS = [
        'billboard' => 'Billboard na stronie głównej',
        'box' => 'Box przy ofertach',
    ];

    protected $fillable = ['name', 'format', 'target_url', 'languages'];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'accepted_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creatives(): HasMany
    {
        return $this->hasMany(AdCreative::class);
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isLive(): bool
    {
        return $this->isAccepted() && $this->active;
    }

    /** Języki, dla których brakuje jeszcze reklamy i artykułu. */
    public function missingLanguages(): array
    {
        $done = $this->creatives()->pluck('locale')->all();

        return array_values(array_diff($this->languages ?? [], $done));
    }

    /** Losowa włączona reklama w danym formacie dla bieżącego języka strony. */
    public static function pick(string $format): ?AdCreative
    {
        return AdCreative::query()
            ->where('locale', app()->getLocale())
            ->whereHas('campaign', fn ($q) => $q->where('format', $format)->where('active', true)->whereNotNull('accepted_at'))
            ->with('campaign')
            ->inRandomOrder()
            ->first();
    }
}
