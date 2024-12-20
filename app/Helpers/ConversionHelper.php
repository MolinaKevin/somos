<?php

namespace App\Helpers;

class ConversionHelper
{
    /**
     * Converts points to money.
     *
     * @param int $points
     * @return float
     */
    public static function pointsToMoney(int $points): float
    {
        
        return $points * 0.01;
    }

    /**
     * Converts money to points.
     *
     * @param float $money
     * @return int
     */
    public static function moneyToPoints(float $money): int
    {
        
        return $money / 0.01;
    }
}

