<?php

namespace App\Models;

use Silber\Bouncer\Database\HasRolesAndAbilities;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRolesAndAbilities;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        //'name', 'email', 'password', 'points', 'pass', 'referrer_pass', 'language'
        'name', 'email', 'password', 'points','language'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url', 
        //'referrals'
    ];


	protected static function booted()
	{
		static::saving(function ($user) {
			if (! $user->isDirty('referrer_pass')) {
				return;
			}

			$original_referrer_pass = $user->getOriginal('referrer_pass');

			if (! is_null($original_referrer_pass)) {
				$user->referrer_pass = $original_referrer_pass;
			}
		});
	}

	/**
	 * Methods
	 */

	/**
     * Calculate referral points based on initial points and referral level.
     *
     * @param float $initialPoints The initial points to base the calculation on.
     * @param int $level The referral level (1-8).
     * @return float
     */
    public function calculateReferralPoints(float $initialPoints, int $level): float
    {
        
        $percentage = 25;

        
        for ($i = 1; $i <= $level; $i++) {
            $percentage /= 2;
        }
        
        if ($level >= 8) {
            return 0;
        }
		
        
        return ($initialPoints * $percentage / 100);
    }

	public function payPointsPurchaseThroughQr($qrCodeData)
	{
		
		$decodedData = $qrCodeData;

		
		$commerce = Commerce::find($decodedData['commerceId']);

		
		$pointsPurchase = $commerce->pointsPurchases()->find($decodedData['pointsPurchaseId']);

		
		$this->payWithPoints($pointsPurchase);
	}

    public function getReferralsCount(int $level): int
    {
        if ($level < 1 || $level > 7) {
            return 0;
        }

        $currentLevelPasses = collect([$this->pass]);

        for ($i = 1; $i <= $level; $i++) {
            
            $currentLevelPasses = User::whereIn('referrer_pass', $currentLevelPasses)->pluck('pass');

            
            if ($i === $level) {
                return $currentLevelPasses->count();
            }
        }

        return 0;
    }

    public function getReferralPurchasePoints()
    {
        return \DB::table('purchase_user_points')
            ->join('purchases', 'purchase_user_points.purchase_id', '=', 'purchases.id')
            ->where('purchase_user_points.user_id', $this->id)
            ->select('purchases.id as purchase_id', 'purchase_user_points.points', 'purchases.created_at')
            ->orderBy('purchases.created_at', 'desc')
            ->get();
    }

    public function getReferralPurchasePointsWithoutUser()
    {
        return \DB::table('purchase_user_points')
            ->join('purchases', 'purchase_user_points.purchase_id', '=', 'purchases.id')
            ->where('purchase_user_points.user_id', $this->id)
            ->where('purchases.user_id', '!=', $this->id) 
            ->select('purchases.id as purchase_id', 'purchase_user_points.points', 'purchases.created_at')
            ->orderBy('purchases.created_at', 'desc')
            ->get();
    }



	/**
	 * Relationships
	 */

    public function commerces()
    {
        return $this->belongsToMany(Commerce::class);
    }

	public function nros()
    {
        return $this->belongsToMany(Nro::class);
    }

	public function referrer()
	{
		return $this->belongsTo(User::class, 'referrer_pass', 'pass');
	}

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function pointsPurchases()
    {
        return $this->hasMany(PointsPurchase::class);
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_pass', 'pass');
    }


}
