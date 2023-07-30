<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;

class Commerce extends Model
{
    use HasFactory;

    protected $with = ['entity'];
	
	/**
	 * Methods
	 */

    public function calculateDonation(float $amount) {
        return $this->donated_points * ($amount / $this->gived_points * 100);
    }

	public function createQrPointsCode(PointsPurchase $pointsPurchase)
	{
		$url = route('pointsPurchase.pay', ['uuid' => $pointsPurchase->uuid]);

		// Genera el código QR
		$qrCode = QrCode::format('png')->size(500)->generate($url);
		
		return $qrCode;
	}

	public function createQrPayCode(Purchase $purchase)
	{
		$url = route('purchase.pay', ['uuid' => $purchase->uuid]);

		// Genera el código QR
		$qrCode = QrCode::format('png')->size(500)->generate($url);
		
		return $qrCode;
	}

	/**
	 * Accessors 
	 */


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
		if ($this->relationLoaded('entity') && $this->entity) {
			if (array_key_exists($key, $this->entity->getAttributes())) {
				$this->entity->$key = $value;
			}
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
    
}
