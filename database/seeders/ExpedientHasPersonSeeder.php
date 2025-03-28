<?php

namespace Database\Seeders;

use App\Models\ExpedientHasPerson;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpedientHasPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        ExpedientHasPerson::factory()->count(5)->create();
    }
}
