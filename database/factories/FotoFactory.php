<?php

namespace Database\Factories;

use App\Models\Foto;
use Illuminate\Database\Eloquent\Factories\Factory;

class FotoFactory extends Factory
{
    protected $model = Foto::class;

    public function definition()
    {
        return [
            'fotable_id' => \App\Models\Commerce::factory(), // O también puede ser otra entidad, como NRO si lo usas
            'fotable_type' => \App\Models\Commerce::class, // El tipo de modelo polimórfico
            'path' => 'fotos/commerces/' . $this->faker->numberBetween(1, 100) . '/' . $this->faker->word . '.jpg', // Simular un path de imagen
        ];
    }
}

