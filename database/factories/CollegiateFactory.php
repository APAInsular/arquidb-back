<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Collegiate;
use App\Models\Person;

class CollegiateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Collegiate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'birth_date' => fake()->date(),
            'nationality' => fake()->word(),
            'banking_entity' => fake()->word(),
            'account_number' => fake()->randomLetter(),
            'college' => fake()->word(),
            'origin_college' => fake()->word(),
            'origin_college_number' => fake()->word(),
            'degree' => fake()->randomElement(["Technical Architect"]),
            'collegiate_number' => fake()->word(),
            'specialty' => fake()->word(),
            'termination_date' => fake()->date(),
            'graduation_date' => fake()->date(),
            'career_end_et' => fake()->word(),
            'web_page' => fake()->word(),
            'council_reg_number' => fake()->word(),
            'situation' => fake()->word(),
        ];
    }
}
