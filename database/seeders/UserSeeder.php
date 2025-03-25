<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            $user->assignRole('visor');
        }

        $superAdmin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('superAdmin');
    }
}
