<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Commerce;
use App\Models\Entity;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Commerce>
 */
class CommerceFactory extends Factory
{

	protected $model = Commerce::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
		return [
        ];
    }
	
} 
