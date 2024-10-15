<?php

namespace App\Models;

use App\Exceptions\InsufficientPointsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid; 

class PointsPurchase extends Model {

    use HasFactory;

    protected $fillable = ['user_id', 'commerce_id', 'points'];

	protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

	/**
	 * Methods
	 */
	
	public function payWithPoints(User $user)
    {
        // Comprueba que el usuario tiene suficientes puntos
        if ($user->points < $this->points) {
            throw new InsufficientPointsException('The user does not have enough points to make this purchase.');
        }

        // Deduce los puntos del total del usuario
        $user->points -= $this->points;
        $user->save();

        // Añade los puntos al total del comercio
        $this->commerce->entity->points += $this->points;
        $this->commerce->save();

        // Asocia el usuario a la compra
        $this->user()->associate($user);
        $this->save();
    }

	/**
	 * Relationships 
	 */

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function commerce() {
        return $this->belongsTo(Commerce::class);
    }

    // Aquí van más métodos relacionados...
}

