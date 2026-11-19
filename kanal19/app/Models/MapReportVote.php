<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapReportVote extends Model
{
    protected $fillable = ['user_id', 'still'];

    protected function casts(): array
    {
        return ['still' => 'boolean'];
    }
}
