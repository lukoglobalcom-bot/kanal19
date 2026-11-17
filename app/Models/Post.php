<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Wpis w Aktualnościach / poradnikach. Każdy wpis jest w jednym języku. */
class Post extends Model
{
    public const CATEGORIES = [
        'pl' => 'Wiadomości z Polski',
        'eu' => 'Wiadomości z Europy',
        'przepisy' => 'Nowe przepisy',
        'mandaty' => 'Mandaty i odwołania',
        'itd' => 'Kontrole ITD',
        'gadzety' => 'Gadżety dla kierowców',
        'pojazdy' => 'Nowe pojazdy',
    ];

    protected $fillable = ['category', 'locale', 'title', 'excerpt', 'body', 'source_url'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Wpisy w bieżącym języku strony. */
    public function scopeForLocale(Builder $q, ?string $locale = null): Builder
    {
        return $q->where('locale', $locale ?? app()->getLocale());
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }
}
