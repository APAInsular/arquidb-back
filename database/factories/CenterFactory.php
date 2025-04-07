<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Center;

class CenterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Center::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'municipality' => fake()->word(),
            'locality' => fake()->word(),
            'street' => fake()->streetName(),
            'number' => fake()->numberBetween(-8, 8),
            'phone' => fake()->numerify('#########'),
        ];
    }
}
