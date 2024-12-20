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
            'fotable_id' => \App\Models\Commerce::factory(), 
            'fotable_type' => \App\Models\Commerce::class, 
            'path' => 'fotos/commerces/' . $this->faker->numberBetween(1, 100) . '/' . $this->faker->word . '.jpg', 
        ];
    }
}

