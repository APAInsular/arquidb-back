<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            PersonSeeder::class,
            CollegiateSeeder::class,
            ClientSeeder::class,
            ExpedientSeeder::class,
            PhaseSeeder::class,
            DocumentSeeder::class,
            RecordSeeder::class,
            PhoneSeeder::class,
            AddressSeeder::class,
            EmailSeeder::class,
        ]);
    }
}
