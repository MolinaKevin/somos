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
        // Imaginamos que 1 punto es igual a 0.01 en dinero
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
        // Imaginamos que 1 punto es igual a 0.01 en dinero
        return $money / 0.01;
    }
}

