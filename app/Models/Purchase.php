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
        $points = round($this->points, 5);
        $user = $this->user;

        $pointsFormatted = number_format($points, 5, '.', '');
        $pointsRounded2  = number_format(round($points, 2), 5, '.', '');

        if ($this->user_id !== null && !$user->referrer && $pointsFormatted === $pointsRounded2) {
            $user->increment('points', $points);
            $user->save();
            \DB::table('purchase_user_points')->insert([
                'purchase_id' => $this->id,
                'user_id'     => $user->id,
                'points'      => $points,
            ]);
            return;
        }

        $originalPoints = $points * 0.25;
        $buyerPoints = floor($originalPoints * 100) / 100;
        $user->increment('points', $buyerPoints);
        $user->save();

        \DB::table('purchase_user_points')->insert([
            'purchase_id' => $this->id,
            'user_id'     => $user->id,
            'points'      => $buyerPoints,
        ]);

        $remainingForSomos = $originalPoints - $buyerPoints;
        $remainingForSomos = floor($remainingForSomos * 100000) / 100000;
        $somos = Somos::latest('id')->first();
        if ($somos) {
            $newPoints = bcadd((string)$somos->points, (string)$remainingForSomos, 5);
            \DB::table('somos')
                ->where('id', $somos->id)
                ->update(['points' => $newPoints]);
            $somos = Somos::find($somos->id);
        }

        $totalDistributed = $buyerPoints;
        $currentUser = $user;
        $level = 1;
        while ($currentUser->referrer && $level < 8) {
            $referralBonus = floor($this->points * (0.25 / pow(2, $level)) * 100) / 100;
            $currentUser->referrer->increment('points', $referralBonus);
            $currentUser->referrer->save();

            \DB::table('purchase_user_points')->insert([
                'purchase_id' => $this->id,
                'user_id'     => $currentUser->referrer->id,
                'points'      => $referralBonus,
            ]);

            $totalDistributed += $referralBonus;
            $currentUser = $currentUser->referrer;
            $level++;
        }

        $this->gived_to_users_points = $totalDistributed;
        $this->donated_points = $points - $totalDistributed;
        if (!$currentUser->referrer || $level > 8) {
            $this->commerce->donated_points += $points - $totalDistributed;
        }
        $this->save();

        $this->commerce->increment('gived_points', $points);
        $this->commerce->increment('donated_points', $points - $totalDistributed);
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
        $value = $this->amount * $this->commerce->percent / 100;
        return $value;
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
