<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Nro extends Model
{
    use HasFactory;

    protected $fillable = ['somos_id', 'contributed_points', 'to_contribute', 'name', 'description', 'address', 'city', 
        'plz', 'email', 'phone_number', 'website', 'opening_time', 'closing_time', 
        'latitude', 'longitude', 'points', 'percent', 'accepted', 'active',
    ];
    protected $appends = ['is_open', 'avatar_url', 'background_image', 'fotos_urls'];
    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'accepted' => 'boolean',
        'active' => 'boolean',
    ];

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
            'points' => $contributionAmount,
            'somos_id' => $this->somos->id,
        ]);

        $this->contributions()->save($contribution);

        // Retorna el monto de la contribución para poder realizar cualquier otro cálculo o comprobación que necesites
        return $contributionAmount;
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
            return $foto ? asset('storage/'. $foto->path) : asset('storage/fotos/nros/default_background.jpg');
        }

        // Si no tiene imagen de fondo, devolver una URL por defecto
        return asset('storage/fotos/nros/default_background.jpg');
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

    public static function getNrosWithPhotos()
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

	public function somos()
	{
		return $this->belongsTo(Somos::class);
	}

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
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
