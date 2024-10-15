<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Commerce;

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
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'plz' => $this->faker->postcode,
            'email' => $this->faker->companyEmail,
            'phone_number' => $this->faker->phoneNumber,
            'website' => $this->faker->domainName,
            'opening_time' => '09:00',
            'closing_time' => '17:30',
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'points' => $this->faker->numberBetween(0, 100),
            'percent' => $this->faker->randomFloat(2, 1, 20),
            'active' => false,
            'accepted' => false,
            'donated_points' => 0,
            'gived_points' => 0,
        ];
    }
	
} 
