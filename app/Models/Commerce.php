<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class Commerce extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'address', 'city', 'plz', 'email',
        'phone_number', 'website', 'opening_time', 'closing_time',
        'latitude', 'longitude', 'points', 'percent', 'donated_points',
        'gived_points', 'active', 'accepted', 'background_image_id'
    ];
	
    protected $appends = ['is_open', 'avatar_url', 'background_image', 'fotos_urls'];

    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'active' => 'boolean',
        'accepted' => 'boolean',
    ];

	/**
	 * Methods
	 */

    public function calculateDonation(float $points) {
        return $this->donated_points * ($points / $this->gived_points * 100);
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


    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            // Si el avatar está presente, devolver la URL completa
            return asset('storage/' . $this->avatar);
        }

        // Si no hay avatar, devolver la URL del avatar por defecto
        return asset('storage/avatars/avatar_fake.png');
    }

    public function getBackgroundImageAttribute()
    {
        // Si el comercio tiene una imagen de fondo asociada, devolver la URL
        if ($this->background_image_id) {
            $foto = Foto::find($this->background_image_id);
            return $foto ? asset('storage/'. $foto->path) : asset('storage/fotos/commerces/default_background.jpg');
        }

        // Si no tiene imagen de fondo, devolver una URL por defecto
        return asset('storage/fotos/commerces/default_background.jpg');
    }

    public function getFotosUrlsAttribute()
    {
        return $this->fotos
            ->filter(function ($foto) {
                // Excluir la foto que está asignada como background_image
                return $foto->id !== $this->background_image_id;
            })
            ->map(function ($foto) {
                return asset('storage/' . $foto->path);
            })
            ->values(); // Asegurarse de reiniciar las claves de la colección
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

	/**
	 * Relationships
	 */

	public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function categories() 
    {
        return $this->belongsToMany(Category::class);
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


}
