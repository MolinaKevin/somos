<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class L10n extends Model
{
    use HasFactory;

    // Definir la tabla si no es plural o sigue un patrón diferente
    protected $table = 'l10ns';

    // Definir los campos que se pueden llenar de manera masiva
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    // Definir los cast para asegurar que los valores sean correctos
    protected $casts = [
        'locale' => 'string',
        'group' => 'string',
        'key' => 'string',
        'value' => 'string',
    ];

    // Agregar reglas de validación (si es necesario para la creación)
    public static $rules = [
        'locale' => 'required|string|max:5', // idioma como 'es', 'en', etc.
        'group' => 'required|string|max:50',  // grupo de traducción, como 'auth', 'language'
        'key' => 'required|string|max:100',   // clave, como 'login', 'logout', etc.
        'value' => 'required|string|max:255', // valor de la traducción
    ];

    public static function boot()
    {
        parent::boot();

        // Exportar las traducciones a un archivo JSON después de crear o actualizar
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

