<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Kierowca, który zgłosił „Jadę pomóc” do wezwania SOS. */
class SosResponse extends Model
{
    protected $fillable = ['user_id'];

    public function helper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
