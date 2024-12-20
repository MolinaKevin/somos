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
	
    protected $appends = [
        'money', 
        'points', 
    ];

	/**
	 * Methods
	 */

    public function distributePoints()
    {
        $points = $this->points; 
        $user = $this->user; 


        $givedToUserPoints = $this->points * 0.25;
        $user->increment('points', $givedToUserPoints);
        $user->save();


        
        \DB::table('purchase_user_points')->insert([
            'purchase_id' => $this->id,
            'user_id' => $user->id,
            'points' => $givedToUserPoints,
        ]);

        $level = 1; 

        while ($user->referrer && $level <= 8) {
            
            $referralPoints = $user->calculateReferralPoints($points, $level);

            
            $user->referrer->increment('points', $referralPoints);

            
            \DB::table('purchase_user_points')->insert([
                'purchase_id' => $this->id,
                'user_id' => $user->referrer->id,
                'points' => $referralPoints,
            ]);

            $givedToUserPoints += $referralPoints;

            
            $level++;
            $user = $user->referrer;
        }

        
        $this->gived_to_users_points = $givedToUserPoints;
        $this->donated_points = $this->points - $givedToUserPoints;


        
        if (!$user->referrer || $level > 8) {
            $this->commerce->donated_points += $this->points - $givedToUserPoints;
        }

        $this->save();

        
        $this->commerce->increment('gived_points', $this->points);
        $this->commerce->increment('donated_points', $this->points - $givedToUserPoints);
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

    public function getMoneyAttribute()
    {
        return $this->amount / 100;
    }
	public function getPointsAttribute()
	{
		return $this->money * 100 * $this->commerce->percent / 100; 
	}

    public function getUserPointsReceivedAttribute()
    {
        return $this->pointsDistribution->where('user_id', $this->user_id)->sum('points');
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

    public function pointsDistribution()
    {
        return $this->hasMany(PurchaseUserPoint::class);
    }

	
}
