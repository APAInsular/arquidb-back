<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Person;

class PersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Person::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'identification_type' => fake()->randomElement(["DNI","NIF"]),
            'identification_number' => fake()->randomLetter(),
            'name' => fake()->name(),
            'first_surname' => fake()->word(),
            'second_surname' => fake()->word(),
            'observations' => fake()->word(),
        ];
    }
}
