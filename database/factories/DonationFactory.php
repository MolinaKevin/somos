<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\Cashout;
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
            'cashout_id' => Cashout::factory(),
            'points' => $this->faker->numberBetween(10, 100),
            'donated_points' => $this->faker->numberBetween(10, 100),
            'is_paid' => $this->faker->boolean,
        ];
    }
}

