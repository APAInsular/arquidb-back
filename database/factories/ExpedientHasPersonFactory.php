<?php

namespace Database\Factories;

use App\Models\Expedient;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpedientHasPerson>
 */
class ExpedientHasPersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'expedient_id' => Expedient::factory(),
            'person_id' => Person::factory(),
        ];
    }
}
