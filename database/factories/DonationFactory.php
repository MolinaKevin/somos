<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\Closure;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Donation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'commerce_id' => Commerce::factory(),
            'nro_id' => Nro::factory(),
            'closure_id' => Closure::factory(),
            'amount' => $this->faker->numberBetween(10, 100),
            'donated_amount' => $this->faker->numberBetween(10, 100),
            'is_paid' => $this->faker->boolean,
        ];
    }
}

