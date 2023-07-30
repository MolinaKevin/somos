<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Closure extends Model
{
    use HasFactory;

    protected $fillable = [
        'commerce_id',
        'amount',
    ];

    protected $commerce;

    public function setCommerce(Commerce $commerce) {
        $this->commerce = $commerce;
    }


    public function perform() {
        // Realiza la conversión de puntos a dinero
        $this->amount = $this->commerce->gived_points * config('somossettings.exchange_rate');

        // Establece los puntos del comercio a 0
        $this->commerce->gived_points = 0;

        // Guarda el comercio
        $this->commerce->save();
    }
}
