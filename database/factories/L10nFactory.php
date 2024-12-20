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
            'locale' => $this->faker->languageCode,  
            'group' => $this->faker->word,            
            'key' => $this->faker->word,              
            'value' => $this->faker->sentence,        
        ];
    }
}

