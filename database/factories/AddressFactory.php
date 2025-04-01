<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Address;
use App\Models\Person;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'country' => fake()->country(),
            'province' => fake()->word(),
            'municipality' => fake()->word(),
            'locality' => fake()->word(),
            'street' => fake()->streetName(),
            'number' => fake()->numberBetween(-8, 8),
            'postal_code' => fake()->randomLetter(),
        ];
    }
}
