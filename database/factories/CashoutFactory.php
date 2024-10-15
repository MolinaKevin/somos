<?php

namespace Database\Factories;

use App\Models\Cashout;
use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashoutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cashout::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'commerce_id' => function() {
                return Commerce::factory()->create()->id;
            },
            'points' => $this->faker->randomFloat(2, 0, 1000), // genera un número flotante aleatorio
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Cashout $cashout) {
            $cashout->setCommerce(Commerce::find($cashout->commerce_id));
        });
    }
}

