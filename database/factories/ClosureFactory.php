<?php

namespace Database\Factories;

use App\Models\Closure;
use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClosureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Closure::class;

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
            'amount' => $this->faker->randomFloat(2, 0, 1000), // genera un número flotante aleatorio
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Closure $closure) {
            $closure->setCommerce(Commerce::find($closure->commerce_id));
        });
    }
}

