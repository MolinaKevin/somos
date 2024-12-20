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
        
        $this->points = $this->getAmount();

        
        $this->commerce->gived_points = 0;

        
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
