<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class User extends Authenticatable
{
    use HasApiTokens;
	use HasRoles;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'points', 'pass', 'referrer_pass'
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
        // El porcentaje inicial es 25%
        $percentage = 25;

        // Reducimos el porcentaje a la mitad con cada nivel
        for ($i = 1; $i <= $level; $i++) {
            $percentage /= 2;
        }
        // No entregamos puntos de referido más allá del nivel 8
        if ($level >= 8) {
            return 0;
        }
		
        // Calculamos y devolvemos los puntos de referido
        return ($initialPoints * $percentage / 100);
    }

	public function payPointsPurchaseThroughQr($qrCodeData)
	{
		// No need to decode, $qrCodeData is already an array
		$decodedData = $qrCodeData;

		// Search the commerce using the commerceId from the decoded data
		$commerce = Commerce::find($decodedData['commerceId']);

		// Use the commerce to find the PointsPurchase
		$pointsPurchase = $commerce->pointsPurchases()->find($decodedData['pointsPurchaseId']);

		// Pay for the PointsPurchase with the user's points
		$this->payWithPoints($pointsPurchase);
	}

	/**
	 * Relationships
	 */

	public function commerces()
    {
        return $this->morphedByMany(Commerce::class, 'entityable');
    }
	

	public function nros()
    {
        return $this->morphedByMany(Nro::class, 'entityable');
    }

	public function referrer()
	{
		return $this->belongsTo(User::class, 'referrer_pass', 'pass');
	}

}
