<?php

namespace Database\Factories;

use App\Models\Contribution;
use App\Models\Nro;
use App\Models\Somos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contribution>
 */
class ContributionFactory extends Factory
{
    protected $model = Contribution::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'points' => $this->faker->randomFloat(2, 0, 1000), 
            'nro_id' => Nro::factory(), 
            'somos_id' => Somos::factory(), 
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

