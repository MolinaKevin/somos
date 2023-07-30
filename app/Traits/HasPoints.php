<?php

namespace App\Traits;

trait HasPoints
{
    /**
     * Returns the 'money' attribute based on 'amount' or 'points' attribute.
     */
    public function getMoneyAttribute()
    {
        // Imaginamos que 1 punto es igual a 0.01 en dinero
        $conversionRate = 0.01;

        if (isset($this->attributes['points'])) {
            return $this->attributes['points'] * $conversionRate;
        }

        if (isset($this->attributes['amount'])) {
            return $this->attributes['amount'] * $conversionRate;
        }

        return null;
    }

    /**
     * Transform a given amount of points into money.
     * 
     * @param float $points
     * 
     * @return float
     */
    public function transformPointsToMoney(float $points): float
    {
        // Imaginamos que 1 punto es igual a 0.01 en dinero
        return $points * 0.01;
    }
}

