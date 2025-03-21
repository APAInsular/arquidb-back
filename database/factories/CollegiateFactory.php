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
            'birthDate' => fake()->date(),
            'nationality' => fake()->word(),
            'bankingEntity' => fake()->word(),
            'accountNumber' => fake()->randomLetter(),
            'college' => fake()->word(),
            'originCollege' => fake()->word(),
            'originCollegeNumber' => fake()->word(),
            'degree' => fake()->randomElement(["Technical Architect","Architect"]),
        ];
    }
}
