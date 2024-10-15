<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'fotable_id', 'fotable_type'];

    /**
     * Accesors
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    /**
     * Relationships
     */
    public function fotable()
    {
        return $this->morphTo();
    }
}

