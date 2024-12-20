<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Enums\SealState;

class Seal extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'image'];

    protected $temporaryImage;

    protected $appends = ['image', 'translated_name'];


    protected static function booted()
    {
        static::creating(function ($seal) {
            if (empty($seal->slug)) {
                $seal->slug = Str::slug($seal->name);
            }
        });

        static::created(function ($seal) {
            if ($seal->temporaryImage instanceof UploadedFile) {
                $seal->storeImage($seal->temporaryImage);
                $seal->temporaryImage = null;
            }
        });
    }

    public function storeImage(UploadedFile $image)
    {
        $path = $image->store("seals/{$this->id}", 'public');
        $this->image = $path;
        $this->saveQuietly();
    }

    public function setImageAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            $this->temporaryImage = $value;
        } else {
            $this->attributes['image'] = $value;
        }
    }

    /**
     * Getter para el atributo `image`.
     * Usa el estado asociado para determinar la imagen a devolver.
     */
    public function getImageAttribute()
    {
        $basePath = "seals/{$this->id}/";
        $defaultPath = "seals/default/";

        if (Storage::disk('public')->exists($basePath)) {
            return "{$basePath}::STATE::.svg";
        }

        return "{$defaultPath}::STATE::.svg";
    }

    public function getTranslatedNameAttribute()
    {
        $locale = Auth::user()->language ?? 'en'; 
        $translation = L10n::where('locale', $locale)
            ->where('group', 'seal')
            ->where('key', $this->slug)
            ->first();

        return $translation->value ?? $this->name;
    }

    /**
     * Obtiene la URL de la imagen correspondiente al estado del sello.
     *
     * @param string|int|null $state
     * @return string
     */
    public function getImageForStateFromPivot($state = null): string
    {
        $normalizedState = is_numeric($state)
            ? SealState::from($state)->name
            : (SealState::tryFrom($state) ? SealState::from($state)->name : SealState::NONE->name);

        $specificPath = "seals/{$this->id}/" . strtolower($normalizedState) . ".svg";
        $defaultPath = "seals/default/" . strtolower($normalizedState) . ".svg";

        
        if (Storage::disk('public')->exists($specificPath)) {
            return Storage::disk('public')->url($specificPath);
        }

        
        return Storage::disk('public')->url($defaultPath);
    }



    /**
     * Normaliza el estado del sello.
     *
     * @param string|int|null $state
     * @return string
     */
    private function normalizeState($state): string
    {
        if (is_numeric($state)) {
            try {
                return SealState::from((int) $state)->name;
            } catch (\ValueError $e) {
                return SealState::NONE->name;
            }
        }

        if (SealState::tryFrom($state)) {
            return SealState::from($state)->name;
        }

        return SealState::NONE->name;
    }

    
    public function commerces()
    {
        return $this->belongsToMany(Commerce::class)->withPivot('state');
    }
}

