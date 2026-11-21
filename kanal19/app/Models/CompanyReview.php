<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Opinia kierowcy o pracodawcy. Ukrywana po 3 zgłoszeniach (do decyzji admina). */
class CompanyReview extends Model
{
    public const HIDE_AFTER_REPORTS = 3;

    public const ASPECTS = [
        'pay' => 'Wypłaty na czas',
        'vehicles' => 'Stan pojazdów',
        'atmosphere' => 'Atmosfera i dyspozytor',
    ];

    protected $fillable = ['rating', 'pay', 'vehicles', 'atmosphere', 'worked_there', 'comment'];

    protected function casts(): array
    {
        return ['worked_there' => 'boolean', 'hidden' => 'boolean', 'replied_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CompanyReviewReport::class);
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('hidden', false);
    }

    /** Podpis autora: „Marek N.” (bez pełnego nazwiska). */
    public function signature(): string
    {
        $p = $this->author?->driverProfile;

        return $p ? $p->first_name.' '.mb_substr($p->last_name, 0, 1).'.' : __('Kierowca');
    }

    /** Średnia i liczba opinii o firmie: ['avg' => 4.3, 'count' => 12] albo null. */
    public static function summaryFor(int $companyId): ?array
    {
        $row = static::visible()->where('company_id', $companyId)->selectRaw('AVG(rating) a, COUNT(*) c')->first();

        return $row && $row->c > 0 ? ['avg' => round((float) $row->a, 1), 'count' => (int) $row->c] : null;
    }
}
