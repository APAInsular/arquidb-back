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
            'phase' => fake()->randomElement(["000","100","200","300","310","400","450","500","550","600","620","640","700","780","800","850","900","911","920","930","940","950","960","970","980"]),
            'title' => fake()->sentence(4),
            'expedient_id' => Expedient::factory(),
        ];
    }
}
