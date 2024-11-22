<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'parent_id'];

    // Agregar el atributo "translated_name" automáticamente al serializar el modelo
    protected $appends = ['translated_name'];

    /**
     * Bootstrap del modelo para generar automáticamente el slug.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Obtiene el nombre traducido basado en el locale proporcionado.
     *
     * @param string $locale
     * @return string|null
     */
    public function getTranslatedName(string $locale): ?string
    {
        return DB::table('l10ns')
            ->where('locale', $locale)
            ->where('group', 'category')
            ->where('key', $this->slug)
            ->value('value');
    }

    /**
     * Accessor para obtener el atributo "translated_name".
     *
     * @return string
     */
    public function getTranslatedNameAttribute(): string
    {
        $locale = auth()->check() ? auth()->user()->language : 'en'; // Idioma del usuario autenticado o inglés por defecto.
        return $this->translationsByLocale($locale)->value('value') ?? $this->name;
    }

    /**
     * Relación con los comercios asociados a la categoría.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function commerces()
    {
        return $this->belongsToMany(Commerce::class);
    }

    /**
     * Relación con las categorías hijas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Relación con la categoría padre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relación con las traducciones de esta categoría.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(L10n::class, 'key', 'slug')
            ->where('group', 'category');
    }

    /**
     * Relación con las traducciones filtradas por locale.
     *
     * @param string $locale
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translationsByLocale(string $locale): HasMany
    {
        return $this->translations()->where('locale', $locale);
    }
}

