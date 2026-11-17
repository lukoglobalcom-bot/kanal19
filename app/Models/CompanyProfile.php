<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    protected $fillable = ['name', 'nip', 'business_type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
