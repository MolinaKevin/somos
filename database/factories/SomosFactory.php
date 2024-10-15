<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Somos>
 */
class SomosFactory extends Factory
{
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
        ];
    }
}
