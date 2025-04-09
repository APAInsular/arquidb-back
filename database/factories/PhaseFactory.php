<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Expedient;
use App\Models\Phase;

class PhaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Phase::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'phase' => str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
            'title' => fake()->sentence(4),
            'expedient_id' => Expedient::factory(),
        ];
    }
}
