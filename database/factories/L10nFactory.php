<?php

namespace Database\Factories;

use App\Models\L10n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\L10n>
 */
class L10nFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente a esta fábrica.
     *
     * @var string
     */
    protected $model = L10n::class;

    /**
     * Define el estado predeterminado del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'locale' => $this->faker->languageCode,  // Código de idioma aleatorio, como 'en', 'es', etc.
            'group' => $this->faker->word,            // Un grupo aleatorio para la traducción
            'key' => $this->faker->word,              // Una clave aleatoria, como 'welcome', 'login', etc.
            'value' => $this->faker->sentence,        // Un valor aleatorio para la traducción
        ];
    }
}

