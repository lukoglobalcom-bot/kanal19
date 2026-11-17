<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Reklama + artykuł kampanii w jednym języku. */
class AdCreative extends Model
{
    protected $fillable = ['locale', 'headline', 'text', 'image', 'article_title', 'article_body'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }
}
