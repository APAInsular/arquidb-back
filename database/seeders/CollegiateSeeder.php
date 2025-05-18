<?php

namespace Database\Seeders;

use App\Models\Collegiate;
use App\Models\Person;
use Illuminate\Database\Seeder;

class CollegiateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $availablePersons = Person::doesntHave('collegiates')->inRandomOrder()->take(5)->get();

        if ($availablePersons->count() < 5) {
            $toCreate = 5 - $availablePersons->count();
            $newPersons = Person::factory()->count($toCreate)->create();
            $availablePersons = $availablePersons->merge($newPersons);
        }

        foreach ($availablePersons as $person) {
            Collegiate::factory()->create([
                'person_id' => $person->id
            ]);
        }
    }
}
