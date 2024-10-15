<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Somos extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'plz',
        'email',
        'phone_number',
        'website',
        'operating_hours',
        'latitude',
        'longitude',
        'points',
    ];

    public function nros()
    {
        return $this->hasMany(Nro::class);
    }

}
