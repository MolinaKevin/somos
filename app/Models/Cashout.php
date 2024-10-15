<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPoints;

class Cashout extends Model
{
    use HasFactory, HasPoints;

    protected $fillable = [
        'commerce_id',
        'points',
    ];

    protected $commerce;

    public function setCommerce(Commerce $commerce) {
        $this->commerce = $commerce;
    }


    public function perform() {
        // Realiza la conversión de puntos a dinero
        $this->points = $this->getAmount();

        // Establece los puntos del comercio a 0
        $this->commerce->gived_points = 0;

        // Guarda el comercio
        $this->commerce->save();
    }

    private function getAmount() : float
    {
        return $this->commerce->gived_points * config('somossettings.exchange_rate');
    }

    /**
     * Relationships
     */

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

}
