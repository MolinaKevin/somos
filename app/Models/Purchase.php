<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid; 
use Carbon\Carbon;
use App\Traits\HasPoints;

class Purchase extends Model
{
    use HasFactory, HasPoints;

  	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'amount', 'gived_to_users_points', 'donated_points', 'paid', 'commerce_id', 'user_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
            $model->gived_to_users_points = $model->gived_to_users_points ?? 0.0;
            $model->donated_points = $model->donated_points ?? 0.0;
        });
    }
	
	/**
	 * Methods
	 */

	public function distributePoints()
	{
		$points = $this->points; // puntos totales a distribuir
		$user = $this->user; // usuario que hizo la compra

		$givedToUserPoints = $this->points * 0.25;
		$user->increment('points',$givedToUserPoints);	
		$user->save();
		$level = 1; // nivel de referencia


		while ($user->referrer && $level <= 8) {
			// Calcula los puntos del referido
			$referralPoints = $user->calculateReferralPoints($points, $level);

			// Actualiza los puntos del referido
			$user->referrer->increment('points', $referralPoints);

			$givedToUserPoints += $referralPoints;

			// Sube un nivel y pasa al siguiente usuario referido
			$level++;
			$user = $user->referrer;
		
		}

		// Actualizamos los puntos que se dieron a los usuarios
		$this->gived_to_users_points = $givedToUserPoints;
		$this->donated_points = $this->points - $givedToUserPoints;

		if ($this->gived_to_users_points + $this->donated_points != $this->points) {
			dd("BIG PROBLEM");
		}

		// Si ha alcanzado el final de la cadena de referidos, asigna los puntos restantes a donated_points
		if (!$user->referrer || $level > 8) {
			$this->commerce->donated_points += $this->points - $givedToUserPoints;
		}

		$this->save();

		// Los puntos restantes se asignan al comercio
		$this->commerce->increment('gived_points', $this->points);
		$this->commerce->increment('donated_points', $this->points -  $givedToUserPoints);
	}
	
	public function isPaid() {
		return (bool) $this->user;
	}

	public function pay(User $user) {
		$this->user()->associate($user);
		$this->save();
		$this->load('user');

		$this->refresh();
		$this->distributePoints();

		$this->paid_at = Carbon::now();

		$this->save();
	}

	/**
	 * Accessors 
	 */

	public function getPointsAttribute()
	{
		return $this->money * 100 * $this->commerce->percent / 100; 
	}

	/**
	 * Relationships 
	 */

	public function user()
    {
        return $this->belongsTo(User::class);
    }	

	public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }	
	
}
