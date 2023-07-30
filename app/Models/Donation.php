<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'commerce_id',
        'nro_id',
        'amount',
        'donated_amount',
        'is_paid',
        'closure_id',
    ];

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    public function nro()
    {
        return $this->belongsTo(Nro::class);
    }

    public function closure()
    {
        return $this->belongsTo(Closure::class);
    }
}
