<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPoints;

class Donation extends Model
{
    use HasFactory, HasPoints;

    protected $fillable = [
        'commerce_id',
        'nro_id',
        'points',
        'donated_points',
        'is_paid',
        'cashout_id',
    ];

    /**
     * Relations
     */

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
