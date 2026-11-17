<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverProfile extends Model
{
    protected $fillable = ['first_name', 'last_name', 'experience', 'vehicles', 'licences', 'in_database'];

    protected function casts(): array
    {
        return [
            'vehicles' => 'array',
            'licences' => 'array',
            'in_database' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
