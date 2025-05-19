<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Person;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $availablePersons = Person::doesntHave('client')->inRandomOrder()->take(5)->get();

        if ($availablePersons->count() < 5) {
            $toCreate = 5 - $availablePersons->count();
            $newPersons = Person::factory()->count($toCreate)->create();
            $availablePersons = $availablePersons->merge($newPersons);
        }

        foreach ($availablePersons as $person) {
            Client::factory()->create([
                'person_id' => $person->id
            ]);
        }
    }
}
