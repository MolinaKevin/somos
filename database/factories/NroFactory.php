<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Entity;
use App\Models\Nro;
use App\Models\Somos;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Nro>
 */
class NroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'somos_id' => Somos::factory()->create()->id,
        ];
    }
	
}
