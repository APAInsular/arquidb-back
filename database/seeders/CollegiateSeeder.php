<?php

namespace Database\Seeders;

use App\Models\Collegiate;
use Illuminate\Database\Seeder;

class CollegiateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Collegiate::factory()->count(5)->create();
    }
}
