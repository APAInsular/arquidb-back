<?php

namespace Database\Seeders;

use App\Models\ExpedientHasPerson;
use App\Models\Expedient;
use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ExpedientHasPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expedients = Expedient::all();
        $people = Person::all();

        foreach ($expedients as $expedient) {
            $randomPeople = $people->random(2); // o cualquier número
            foreach ($randomPeople as $person) {
                ExpedientHasPerson::factory()->create([
                    'expedient_id' => $expedient->id,
                    'person_id' => $person->id,
                    'role' => Arr::random(['collegiate', 'client']),
                ]);
                // $expedient->people()->syncWithoutDetaching($person->id);
            }
        }
    }
}
