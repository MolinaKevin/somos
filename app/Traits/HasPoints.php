<?php

namespace App\Traits;

trait HasPoints
{
    /**
     * Formatea los números eliminando ceros no necesarios.
     * Este método devuelve un string solo para mostrar el valor.
     */
    public function formatNumber($value)
    {
        return rtrim(rtrim(number_format($value, 3, ',', '.'), '0'), ',');
    }

    /**
     * Accesor para 'points' que mantiene el valor como número para operaciones.
     */
    public function getPointsAttribute($value) 
    {
        return $value; 
    }

    /**
     * Accesor para 'donated_points'.
     */
    public function getDonatedPointsAttribute($value)
    {
        return $value; 
    }

    /**
     * Accesor para 'gived_to_users_points'.
     */
    public function getGivedToUsersPointsAttribute($value)
    {
        return $value; 
    }

    /**
     * Método para obtener el valor formateado de 'points'.
     */
    public function getFormattedPointsAttribute() 
    {
        return $this->formatNumber($this->points);
    }

    /**
     * Método para obtener el valor formateado de 'donated_points'.
     */
    public function getFormattedDonatedPointsAttribute() 
    {
        return $this->formatNumber($this->donated_points);
    }

    /**
     * Método para obtener el valor formateado de 'gived_to_users_points'.
     */
    public function getFormattedGivedToUsersPointsAttribute() 
    {
        return $this->formatNumber($this->gived_to_users_points);
    }
}

