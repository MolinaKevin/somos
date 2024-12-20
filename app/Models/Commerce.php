<?php

namespace App\Models;

use App\Enums\SealState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class Commerce extends Model
{
    use HasFactory;

    const SEAL_STATE_NONE = 0;
    const SEAL_STATE_PARTIAL = 1;
    const SEAL_STATE_FULL = 2;

    protected $fillable = [
        'name', 'description', 'address', 'city', 'plz', 'email',
        'phone_number', 'website', 'opening_time', 'closing_time',
        'latitude', 'longitude', 'points', 'percent', 'donated_points',
        'gived_points', 'active', 'accepted', 'background_image_id'
    ];
	
    protected $appends = ['is_open', 'avatar_url', 'background_image', 'fotos_urls', 'category_ids', 'seals_with_state', 'seal_ids'];

    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'active' => 'boolean',
        'accepted' => 'boolean',
    ];

    protected $hidden = ['categories', 'seals'];

	/**
	 * Methods
	 */

    public function calculateDonation(float $points) {
        return $this->donated_points * ($points / $this->gived_points * 100);
    }

	public function createQrPointsCode(PointsPurchase $pointsPurchase)
	{
		$url = route('pointsPurchase.pay', ['uuid' => $pointsPurchase->uuid]);

		
		$qrCode = QrCode::format('png')->size(500)->generate($url);
		
		return $qrCode;
	}

	public function createQrPayCode(Purchase $purchase)
	{
		$url = route('purchase.pay', ['uuid' => $purchase->uuid]);

		
		$qrCode = QrCode::format('png')->size(500)->generate($url);
		
		return $qrCode;
	}

    /**
     * Filtra comercios según categorías y sellos.
     *
     * @param Builder $query
     * @param array $categoryIds
     * @param array $seals
     * @return Builder
     */
    public static function filterBy(array $categoryIds = [], array $seals = []): Builder
    {
        $query = self::query();

        if (!empty($categoryIds)) {
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        if (!empty($seals)) {
            $query->where(function ($query) use ($seals) {
                foreach ($seals as $sealFilter) {
                    $query->whereHas('seals', function ($q) use ($sealFilter) {
                        $q->where('seal_id', $sealFilter['id'])
                          ->where('state', '>=', $sealFilter['state']);
                    });
                }
            });
        }

        return $query;
    }

	/**
	 * Accessors 
	 */


    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            
            return asset('storage/' . $this->avatar);
        }

        
        return asset('storage/avatars/avatar_fake.png');
    }

    public function getBackgroundImageAttribute()
    {
        
        if ($this->background_image_id) {
            $foto = Foto::find($this->background_image_id);
            return $foto ? asset('storage/'. $foto->path) : asset('storage/fotos/commerces/default_background.jpg');
        }

        
        return asset('storage/fotos/commerces/default_background.jpg');
    }

    public function getFotosUrlsAttribute()
    {
        return $this->fotos
            ->filter(function ($foto) {
                
                return $foto->id !== $this->background_image_id;
            })
            ->map(function ($foto) {
                return asset('storage/' . $foto->path);
            })
            ->values(); 
    }

    public function getIsOpenAttribute()
    {
        $now = Carbon::now();
        $openingTime = Carbon::parse($this->opening_time);
        $closingTime = Carbon::parse($this->closing_time);

        return $now->between($openingTime, $closingTime);
    }
    
    public function getOpeningTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getClosingTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public static function getCommercesWithPhotos()
    {
        return self::has('fotos')->with('fotos')->get();
    }

    public function getCategoryIdsAttribute()
    {
        return $this->categories->pluck('id')->toArray();
    }

    public function getSealIdsAttribute()
    {
        return $this->seals->pluck('id')->toArray();
    }

    public static function getSealStateText($state)
    {
        $states = [
            self::SEAL_STATE_NONE => 'none',
            self::SEAL_STATE_PARTIAL => 'partial',
            self::SEAL_STATE_FULL => 'full',
        ];

        return $states[$state] ?? 'unknown';
    }

    public function getSealsWithStateAttribute()
    {
        return $this->seals->map(function ($seal) {
            return [
                'id' => $seal->id,
                'state' => SealState::from($seal->pivot->state)->label(), 
            ];
        })->toArray();
    }


	/**
	 * Relationships
	 */

	public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_commerce', 'commerce_id', 'category_id')
                    ->withPivot('id as pivot_id');
    }

    public function purchases() 
    {
        return $this->hasMany(Purchase::class);
    }

    public function cashouts() 
    {
        return $this->hasMany(Cashout::class);
    }

    public function donations() 
    {
        return $this->hasMany(Donation::class);
    }

    public function fotos()
    {
        return $this->morphMany(Foto::class, 'fotable');
    }

    public function seals()
    {
        return $this->belongsToMany(Seal::class)->withPivot('state');
    }


}
