<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePhoto extends Model
{
    protected $fillable = ['path', 'position'];

    public function url(): string
    {
        return asset($this->path);
    }
}
