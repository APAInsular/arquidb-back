<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Expedient;

class ExpedientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expedient::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'number' => fake()->randomLetter(),
            'startDate' => fake()->dateTime(),
            'endDate' => fake()->dateTime(),
            'description' => fake()->text(),
            'site' => fake()->word(),
            'postalCode' => fake()->randomLetter(),
            'budget' => fake()->randomFloat(2, 0, 9999999.99),
        ];
    }
}
