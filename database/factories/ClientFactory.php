<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Client;
use App\Models\Person;

class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $person = Person::doesntHave('client')->inRandomOrder()->first();

        if (!$person) {
            $person = Person::factory()->create();
        }

        return [
            'person_id' => $person->id,
            'agent' => fake()->word(),
        ];
    }
}
