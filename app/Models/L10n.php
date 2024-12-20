<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class L10n extends Model
{
    use HasFactory;

    
    protected $table = 'l10ns';

    
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    
    protected $casts = [
        'locale' => 'string',
        'group' => 'string',
        'key' => 'string',
        'value' => 'string',
    ];

    
    public static $rules = [
        'locale' => 'required|string|max:5', 
        'group' => 'required|string|max:50',  
        'key' => 'required|string|max:100',   
        'value' => 'required|string|max:255', 
    ];

    public static function boot()
    {
        parent::boot();

        static::saved(function ($l10n) {
            self::exportTranslations($l10n->locale);
        });

        static::deleted(function ($l10n) {
            self::exportTranslations($l10n->locale);
        });
    }

    /**
     * Exporta todas las traducciones de un idioma específico a un archivo JSON en public/lang.
     *
     * @param string $locale
     * @return void
     */
    public static function exportTranslations($locale)
    {
        $translations = self::where('locale', $locale)->get()->groupBy('group')->map(function ($items) {
            return $items->pluck('value', 'key');
        });

        $filePath = public_path("lang/{$locale}.json");
        File::put($filePath, $translations->toJson(JSON_PRETTY_PRINT));
    }
}

