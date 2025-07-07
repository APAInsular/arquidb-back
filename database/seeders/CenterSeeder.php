<?php

namespace Database\Seeders;

use App\Models\Center;
use Illuminate\Database\Seeder;

class CenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Center::factory()->create([
            'name' => 'Colegio Oficial de Arquitectos de Fuerteventura',
            'municipality' => 'Puerto del Rosario',
            'locality' => 'Puerto del Rosario',
            'street' => fake()->streetName(),
            'number' => fake()->numberBetween(-8, 8),
            'phone' => fake()->numerify('#########'),
        ]);

        Center::factory()->count(5)->create();
    }
}
