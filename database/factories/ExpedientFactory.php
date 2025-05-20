<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Expedient;
use App\Models\Center;

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
            'number' => fake()->unique()->regexify('[0-9]{10}'),
            'start_date' => fake()->dateTime(),
            'end_date' => fake()->dateTime(),
            'description' => fake()->text(),
            'site' => fake()->word(),
            'postal_code' => fake()->regexify('[0-9]{5}'),
            'budget' => fake()->randomFloat(2, 0, 9999999.99),
            'center_id' => Center::inRandomOrder()->first()->id,
        ];
    }
}
