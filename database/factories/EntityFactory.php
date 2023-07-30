<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Entity;
use App\Models\Commerce;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entity>
 */
class EntityFactory extends Factory
{

	protected $model = Entity::class;

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
            'operating_hours' => '9am - 5pm',
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'points' => $this->faker->numberBetween(0, 100),
            'percent' => $this->faker->randomFloat(2, 1, 20),
			'entityable_id' => null, 
            'entityable_type' => null, 
        ];
    }


}
