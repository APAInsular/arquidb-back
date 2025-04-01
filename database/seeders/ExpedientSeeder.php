<?php

namespace Database\Seeders;

use App\Models\Expedient;
use Illuminate\Database\Seeder;

class ExpedientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Expedient::factory()->count(5)->create();
    }
}
