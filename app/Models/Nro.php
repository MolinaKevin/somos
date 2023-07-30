<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nro extends Model
{
    use HasFactory;

    protected $with = ['entity'];

	/**
	 * Methods
	 */

    public function contribute()
    {
        // Calcula el monto de la contribución basado en el porcentaje inverso
        //dd($this->percent, $this->contributed_points, (1 - $this->percent/100), (1-$this->percent/100) * $this->contributed_points);
        $contributionAmount = (1 - $this->percent/100) * $this->to_contribute;

        // Asegúrate de que la relación con Somos esté definida en tu modelo Nro
        // Esto incrementará los points de la entidad Somos asociada
        $this->somos->increment('points', $contributionAmount);
        $this->increment('contributed_points', $contributionAmount);
        $this->to_contribute = 0;

        
        $contribution = new Contribution([
            'amount' => $contributionAmount,
            'somos_id' => $this->somos->id,
        ]);

        $this->contributions()->save($contribution);

        // Retorna el monto de la contribución para poder realizar cualquier otro cálculo o comprobación que necesites
        return $contributionAmount;
    }

    /**
     * Accessors
     */

    public function getPercentAttribute() {
        return $this->entity->percent;
    }

	/**
	 * Magic methods for morph relation
	 */

	public function __get($key)
	{
		if (
			array_key_exists($key, $this->attributes) ||
			array_key_exists($key, $this->relations)
		) {
			return parent::__get($key);
		}

		// Check if the entity relation is loaded and if it's not null
		if ($this->relationLoaded('entity') && $this->entity !== null) {
			// If the entity has the attribute, return it
			if (array_key_exists($key, $this->entity->getAttributes())) {
				return $this->entity->$key;
			}
		}

		return parent::__get($key);
	}

	
	public function __set($key, $value)
	{
		if ($this->relationLoaded('entity') && array_key_exists($key, $this->entity->getAttributes())) {
			$this->entity->$key = $value;
		} else {
			parent::__set($key, $value);
		}
	}


	public function save(array $options = [])
	{
		if ($this->relationLoaded('entity')) {
			$this->entity->save();
		}

		return parent::save($options);
	}

    /**
	 * Relationships
	 */
	
    public function users()
    {
        return $this->morphToMany(User::class, 'entityable');
    }

	public function entity()
	{
		return $this->morphOne(Entity::class, 'entityable');
	}

	public function somos()
	{
		return $this->belongsTo(Somos::class);
	}

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

}
