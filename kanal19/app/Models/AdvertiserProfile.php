<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertiserProfile extends Model
{
    protected $fillable = ['brand', 'nip', 'website', 'ad_format'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
